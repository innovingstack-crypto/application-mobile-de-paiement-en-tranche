<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class AuthTap extends AbstractProcessingHandler
{
    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        // Ajouter des informations supplémentaires aux logs d'authentification
        if (isset($record['context']['user_id'])) {
            $record['extra']['user_id'] = $record['context']['user_id'];
        }
        
        if (isset($record['context']['ip'])) {
            $record['extra']['ip'] = $record['context']['ip'];
        }
        
        if (isset($record['context']['user_agent'])) {
            $record['extra']['user_agent'] = $record['context']['user_agent'];
        }
        
        // Vous pouvez ajouter ici une logique pour envoyer les logs à un service externe
        // ou les formater d'une manière spécifique
    }
}
