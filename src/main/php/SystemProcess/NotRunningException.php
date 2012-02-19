<?php

namespace SystemProcess;

class NotRunningException extends \Exception
{
    public function __construct() 
    {
        parent::__construct( 'The process is not running.' );
    }
}
