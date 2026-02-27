<?php

namespace App\Exceptions;

use Exception;

class UnverifiedUserException extends Exception
{
    public $identifier;
    public $verificationMethod;

    public function __construct($identifier, $verificationMethod = 'email')
    {
        parent::__construct('Votre compte n\'est pas encore vérifié. Veuillez vérifier votre email ou téléphone.');
        $this->identifier = $identifier;
        $this->verificationMethod = $verificationMethod;
    }
}
