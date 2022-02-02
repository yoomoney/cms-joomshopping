<?php

namespace YooMoney\Helpers;

/**
 * Класс методов для работы с логами модуля
 */
class Logger
{
    /**
     * Выполняет запись лога в файл
     *
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, $context = array())
    {
        $replace = array();
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $replace['{'.$key.'}'] = $value;
            } else {
                $replace['{'.$key.'}'] = json_encode($value);
            }
        }

        if (!empty($replace)) {
            $message = strtr($message, $replace);
        }

        $fileName = $this->getLogFileName();
        $fd       = @fopen($fileName, 'a');

        if ($fd) {
            flock($fd, LOCK_EX);
            fwrite($fd, date(DATE_ATOM).' ['.$level.'] '.$message."\r\n");
            flock($fd, LOCK_UN);
            fclose($fd);
        }
    }

    /**
     * Возвращает путь к файлу лога
     *
     * @return string
     */
    public function getLogFileName()
    {
        return realpath(JSH_DIR).'/log/pm_yoomoney.log';
    }
}