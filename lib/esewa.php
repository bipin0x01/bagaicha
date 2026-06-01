<?php
/**
 * eSewa ePay v2 HMAC-SHA256 signature helpers.
 */

function esewa_sign(string $signing_string, string $secret_key): string
{
    return base64_encode(hash_hmac('sha256', $signing_string, $secret_key, true));
}

function esewa_field_value_for_signature(string $field, array $data, ?string $raw_json = null): string
{
    if ($raw_json !== null) {
        $pattern = '/"' . preg_quote($field, '/') . '"\s*:\s*("(?:[^"\\\\]|\\\\.)*"|true|false|null|-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/';
        if (preg_match($pattern, $raw_json, $matches)) {
            $raw = $matches[1];
            if ($raw[0] === '"') {
                return (string) json_decode($raw);
            }
            return $raw;
        }
    }

    $value = $data[$field];
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if ($value === null) {
        return 'null';
    }

    return (string) $value;
}

function esewa_build_signing_string(array $fields, array $data, ?string $raw_json = null): string
{
    $parts = [];
    foreach ($fields as $field) {
        $field = trim($field);
        if (!array_key_exists($field, $data)) {
            continue;
        }
        $parts[] = $field . '=' . esewa_field_value_for_signature($field, $data, $raw_json);
    }

    return implode(',', $parts);
}

function esewa_verify_callback_signature(array $response, string $secret_key, ?string $raw_json = null): bool
{
    if (empty($response['signature']) || empty($response['signed_field_names'])) {
        return false;
    }

    $fields = explode(',', $response['signed_field_names']);
    $signing_string = esewa_build_signing_string($fields, $response, $raw_json);
    $expected_signature = esewa_sign($signing_string, $secret_key);

    return hash_equals($expected_signature, $response['signature']);
}
