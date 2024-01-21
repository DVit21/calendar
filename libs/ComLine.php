<?php
/** @noinspection PhpUnused */

namespace libs;


use DateTime;
use Exception;

define('TYPE_STRING', 0);
define('TYPE_INT', 1);


class ComLine
{
    //const TYPE_STRING=0;
    //const TYPE_INT = 1;
    public string $script;
    public array $argv_get = [];
    public array $argv_post = [];

    public function __construct($empty=false)
    {
        $this->script = $_SERVER['SCRIPT_NAME'];
        if(!$empty) {
            $this->argv_get = $_GET;
            $this->argv_post = $_POST;
        }
    }

    public function purgeArgvGet(): void
    {
        $this->argv_get = [];
    }

    public function setArgvGet($param, $value): void
    {
        $this->unsetget($param);
        $this->argv_get[$param] = $value;
    }

    public function getArgvGet($param): bool|string {
        if (isset($this->argv_get[$param])) {
            return htmlspecialchars($this->argv_get[$param]);
        }
        return false;
    }

    public function getArgvGetAsInt(string $param): int
    {
        if (isset($this->argv_get[$param])) {
            return intval($this->argv_get[$param]);
        }

        return 0;
    }

    public function getArgvGetAsFloat($param): float
    {
        if (isset($this->argv_get[$param])) {
            return floatval($this->argv_get[$param]);
        }

        return 0;
    }

    public function getArgvGetAsBool(string $param): bool
    {
        if (isset($this->argv_get[$param])) {
            return true;
        }

        return false;
    }

    public function getArgvGetAsDate(string $param): DateTime
    {
        if (isset($this->argv_get[$param])) {
            try {
                $d = new DateTime($this->argv_get[$param]);
            } catch (Exception) {
                $d = new DateTime("1900-01-01");
            }
        } else {
            $d = new DateTime("now");
        }

        return $d;
    }

    public function getArgvGetAsString(string $param): string
    {
        if (isset($this->argv_get[$param])) {
            $res = $this->argv_get[$param];
            if (is_string($res)) {
                return htmlspecialchars($res, ENT_QUOTES | ENT_HTML5);
            }
        }

        return "";
    }


    /**
     * @return string
     */
    public function CreateString(): string
    {
        $string = $this->script;
        $string .= '?';
        foreach ($this->argv_get as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    if(!is_array($v)) $string .= urlencode($key) . '[]=' . urlencode($v) . '&';
                }
            } else {
                $string .= urlencode($key) . '=' . urlencode($value) . '&';
            }
        }

        return $string;
    }

    public function unsetget($param): void
    {
        if (isset($this->argv_get[$param])) {
            unset($this->argv_get[$param]);
        }
    }

    /** @noinspection PhpUnused */
    public function unsetpost($param): void
    {
        if (isset($this->argv_post[$param])) {
            unset($this->argv_post[$param]);
        }
    }

    public function getArgvPost(string $param): null|string|array {
        if ($this->existPost($param)) {
            if(is_array($this->argv_post[$param])){
                $val=array();
                foreach ($this->argv_post[$param] as $key=> $item){
                    $val[$key]=$item;
                }
                return $val;
            }
            return $this->argv_post[$param];
        }
        return null;
    }

    const argv_integer = 0;

    const argv_string = 1;

    const argv_decimal = 2;

    const argv_email = 3;

    const argv_DateTime = 4;

    const argv_array = 5;

    const argv_clear_string =6;

    private array $filters
        = [
            self::argv_integer => FILTER_VALIDATE_INT,
            self::argv_decimal => FILTER_VALIDATE_FLOAT,
            self::argv_email => FILTER_VALIDATE_EMAIL,
        ];

    /**
     * @throws Exception
     */
    public function getArgvPostasType($param, $type = self::argv_string): float|DateTime|bool|int|array|string {
        switch ($type) {
            case self::argv_string:
                return $this->getArgvPostWithScreening($param);
            case self::argv_clear_string:
                return $this->getArgvPost($param);
            case self::argv_integer:
                $v = $this->getArgvPost($param);
                if (is_null($v)) {
                    return 0;
                }
                if (filter_var($v, FILTER_VALIDATE_INT) !== false) {
                    return intval($v);
                }

                return 0;
            case self::argv_decimal:
                $v = $this->getArgvPost($param);
                if (is_null($v)) {
                    return 0;
                }
                if (filter_var($v, FILTER_VALIDATE_FLOAT) !== false) {
                    return floatval($v);
                }
                return 0;
            case self::argv_email:
                $v = $this->getArgvPost($param);
                if (is_null($v)) {
                    return "";
                }
                if (filter_var($v, FILTER_VALIDATE_EMAIL) !== false) {
                    return $v;
                }
                return "";
            case self::argv_DateTime:
                $v = $this->getArgvPost($param);
                try {
                    return new DateTime($v);
                } catch (Exception) {
                    throw new Exception('Wrong type for POST ' . $param);
                }
            case self::argv_array:
                $v = $this->getArgvPost($param);
                if (is_array($v)) {
                    return $v;
                }
                throw new Exception('Wrong type for POST ' . $param);
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public function getArgvGetasType($param, $type = self::argv_string): float|DateTime|array|bool|int|string {
        switch ($type) {
            case self::argv_string:
                return $this->getArgvGetAsString($param);
            case self::argv_integer:
                $v = $this->getArgvGet($param);
                if (!$v) {
                    return 0;
                }
                if (filter_var($v, FILTER_VALIDATE_INT) !== false) {
                    return intval($v);
                }

                return 0;
            case self::argv_decimal:
                $v = $this->getArgvGet($param);
                if (!$v) {
                    return 0;
                }
                if (filter_var($v, FILTER_VALIDATE_FLOAT) !== false) {
                    return floatval($v);
                }
                throw new Exception('Wrong type for POST ' . $param);
            case self::argv_email:
                $v = $this->getArgvGet($param);
                if (!$v) {
                    return "";
                }
                if (filter_var($v, FILTER_VALIDATE_EMAIL) !== false) {
                    return $v;
                }
                echo gettype($v);
                throw new Exception('Wrong type for POST ' . $param);
            case self::argv_DateTime:
                $v = $this->getArgvGet($param);
                try {
                    return new DateTime($v);
                } catch (Exception) {
                    throw new Exception('Wrong type for POST ' . $param);
                }
            case self::argv_array:
                $v = $this->getArgvGet($param);
                if (!$v) {
                    return $v;
                }
                throw new Exception('Wrong type for POST ' . $param);
        }

        return false;
    }

    public function existGet($param): bool
    {
        if (isset($this->argv_get[$param])) {
            return true;
        }

        return false;
    }

    public function existPost($param): bool
    {
        if (isset($this->argv_post[$param])) {
            return true;
        }

        return false;
    }

    public function exist($param): bool
    {
        if (isset($_REQUEST[$param])) {
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public function getArgvAsType($param, $type = self::argv_string): float|DateTime|array|bool|int|string|null {
        if ($this->existPost($param)) {
            return $this->getArgvPostasType($param, $type);
        }elseif ($this->existGet($param)) {
            return $this->getArgvGetasType($param, $type);
        }
        return null;
    }

    /** @noinspection PhpUnused */
    public function isPostType($param, $type): bool
    {
        if ($this->existPost($param)) {
            if ($type == self::argv_string) {
                return true;
            }
            $filter = $this->filters[$type];
            $v = $this->getArgvPost($param);
            if (filter_var($v, $filter) === false) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $param
     *
     * @return string
     * @throws Exception
     */
    public function getArgvPostWithScreening($param): string
    {
        if ($this->existPost($param)) {
            $res = $this->getArgvPost($param);
            if(is_string($res)) return htmlspecialchars($res ?? "", ENT_QUOTES | ENT_HTML5);
            return "";
        }

        return "";
    }
    public function clearGets(): void {
        $this->argv_get=array();
    }
    public function emptyCommand():string {
        return $this->script;
    }
}

