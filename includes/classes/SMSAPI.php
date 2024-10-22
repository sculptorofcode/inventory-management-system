<?php

class SMSAPI {
    private $baseUrl = "https://www.fast2sms.com/dev/bulkV2";
    private $voiceUrl = "https://www.fast2sms.com/dev/voice";
    private $authorization = SMSAPI_AUTHORIZATION;

    public function sendOTP($numbers, $flash = "0", $variables_values = "") {
        $params = [
            "authorization" => $this->authorization,
            "route" => "otp",
            "variables_values" => $variables_values,
            "numbers" => $numbers,
            "flash" => $flash
        ];
        return $this->sendRequest($params);
    }

    public function sendDLT($numbers, $message = "", $sender_id = "", $flash = "0", $variables_values = "") {
        $params = [
            "authorization" => $this->authorization,
            "route" => "dlt",
            "sender_id" => $sender_id,
            "message" => $message,
            "variables_values" => $variables_values,
            "numbers" => $numbers,
            "flash" => $flash
        ];

        return $this->sendRequest($params);
    }

    public function sendQ($numbers, $message = "", $flash = "0") {
        $params = [
            "authorization" => $this->authorization,
            "route" => "q",
            "message" => $message,
            "numbers" => $numbers,
            "flash" => $flash
        ];

        return $this->sendRequest($params);
    }

    public function sendVoiceOTP($numbers, $flash = "0", $variables_values = "") {
        $params = [
            "authorization" => $this->authorization,
            "route" => "otp",
            "variables_values" => $variables_values,
            "numbers" => $numbers
        ];

        return $this->sendRequest($params, $this->voiceUrl);
    }

    public function sendDLTManual($numbers, $message = "", $sender_id = "", $template_id = "", $entity_id = "", $flash = "0") {
        $params = [
            "authorization" => $this->authorization,
            "route" => "dlt_manual",
            "sender_id" => $sender_id,
            "template_id" => $template_id,
            "entity_id" => $entity_id,
            "message" => $message,
            "numbers" => $numbers,
            "flash" => $flash
        ];

        return $this->sendRequest($params);
    }

    private function sendRequest($params, $url = null) {
        if (is_null($url)) {
            $url = $this->baseUrl;
        }
    
        $queryString = http_build_query($params);
        $fullUrl = $url . '?' . $queryString;
    
        $ch = curl_init($fullUrl);
        
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);

        if ($response === false) {
            return [
                "status" => "error",
                "message" => "Request failed",
                "error" => curl_error($ch),
                'url' => $fullUrl
            ];
        }
    
        curl_close($ch);
    
        $decodedResponse = json_decode($response, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                "status" => "error",
                "message" => "Invalid JSON response",
                "error" => json_last_error_msg()
            ];
        }
    
        return [
            "status" => "success",
            "message" => "Request successful",
            "response" => $decodedResponse
        ];
    }
    
    
}

$smsApi = new SMSAPI();
?>