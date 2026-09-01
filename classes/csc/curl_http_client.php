<?php

namespace local_isycredentials\csc;

defined('MOODLE_INTERNAL') || die();

class curl_http_client implements http_client_interface {
    public function post_form(string $url, array $data, array $headers = [], array $tls_options = []): array {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        return $this->post($url, http_build_query($data, '', '&', PHP_QUERY_RFC3986), $headers, $tls_options);
    }

    public function post_json(string $url, array $data, array $headers = [], array $tls_options = []): array {
        $headers[] = 'Content-Type: application/json';
        return $this->post($url, json_encode($data, JSON_THROW_ON_ERROR), $headers, $tls_options);
    }

    private function post(string $url, string $body, array $headers, array $tls_options): array {
        $headers[] = 'User-Agent: Moodle local_isycredentials/1.0';
        debugging('CSC request: POST ' . $url . ' Body: ' . $this->debug_body($body, $headers), DEBUG_DEVELOPER);

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        if (!empty($tls_options['certificate'])) {
            curl_setopt($curl, CURLOPT_SSLCERT, $tls_options['certificate']);
        }
        if (!empty($tls_options['key'])) {
            curl_setopt($curl, CURLOPT_SSLKEY, $tls_options['key']);
        }
        if (!empty($tls_options['ca_info'])) {
            curl_setopt($curl, CURLOPT_CAINFO, $tls_options['ca_info']);
        }

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false || $http_code < 200 || $http_code >= 300) {
            $response_body = is_string($response) ? trim($response) : '';
            if (strlen($response_body) > 2000) {
                $response_body = substr($response_body, 0, 2000) . '...';
            }
            debugging('CSC response: HTTP ' . $http_code . ' ' . $this->debug_body($response_body), DEBUG_DEVELOPER);
            $details = "CSC request failed with HTTP {$http_code}: {$error}";
            if ($content_type !== false && $content_type !== '') {
                $details .= " (Content-Type: {$content_type})";
            }
            if ($response_body !== '') {
                $details .= " Response: {$response_body}";
            }
            $response_data = json_decode($response_body, true);
            throw new api_exception($http_code, is_array($response_data) ? $response_data : [], $details);
        }

        debugging('CSC response: HTTP ' . $http_code . ' ' . $this->debug_body($response), DEBUG_DEVELOPER);

        if ($http_code === 204 && trim($response) === '') {
            return [];
        }

        try {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \moodle_exception('csc_invalid_response', 'local_isycredentials', '', null, $exception->getMessage());
        }
    }

    private function debug_body(string $body, array $headers = []): string {
        $decoded = json_decode($body, true);
        if (!is_array($decoded) && in_array('Content-Type: application/x-www-form-urlencoded', $headers, true)) {
            parse_str($body, $decoded);
        }
        if (!is_array($decoded)) {
            return substr($body, 0, 2000);
        }
        $this->redact_debug_values($decoded);
        return json_encode($decoded);
    }

    private function debug_headers(array $headers): array {
        return array_map(function(string $header): string {
            if (stripos($header, 'authorization:') === 0) {
                return 'Authorization: [redacted]';
            }
            return $header;
        }, $headers);
    }

    private function redact_debug_values(array &$data): void {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->redact_debug_values($value);
            } else if (preg_match('/secret|token|password|authorization|code|key|certificate/i', (string) $key)) {
                $value = '[redacted]';
            }
        }
    }
}