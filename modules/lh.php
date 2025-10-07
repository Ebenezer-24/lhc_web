<?php

class lh
{
    private static $instance = null;
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function moduleInit()
    {
        // Basic module initialization
    }
    
    public function setSettings($settings)
    {
        // Settings handler
    }
}
