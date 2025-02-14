<?php

class Auth
{
        // Properties
        private string $key, $secret, $token, $accountId;
    
        // Constructor
        public function __construct(string $key, string $secret, string $token, string $accountId) {
            $this->key       = $key;
            $this->secret    = $secret;
            $this->token     = $token;
            $this->accountId = $accountId;
        }

        private function validateCredentials(): void
        {
            
        }
}

?>