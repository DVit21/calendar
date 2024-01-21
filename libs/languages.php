<?php

namespace libs;

class languages
{
    private string $lang;
    private array $phrases;
    public function __construct(string $lang="ru")
    {
        $this->lang=$lang;
        $file='config/lang_'.$this->lang.'.json';
        if(file_exists($file)){
            $this->phrases=json_decode(file_get_contents($file),true);
        } else {
            $this->phrases=array();
        }
    }
    public function show(string $phrase):string {
        if( isset($this->phrases[$phrase])){
            return $this->phrases[$phrase];
        }
        return $phrase;
    }
}