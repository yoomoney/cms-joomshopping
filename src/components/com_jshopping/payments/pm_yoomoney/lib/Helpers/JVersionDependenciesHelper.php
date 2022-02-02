<?php

namespace YooMoney\Helpers;

/**
 * В данный класс вынесены функции, для совместимости модуля Yoomoney с версиями Joomla! 3.* и 4.*
 */
class JVersionDependenciesHelper
{
    private $joomlaVersion;

    public function __construct()
    {
        $this->joomlaVersion = (version_compare(JVERSION, '3.0', '<') == 1) ? 2 : 3;
        $this->joomlaVersion = (version_compare(JVERSION, '4.0', '<') == 1) ? $this->joomlaVersion : 4;
    }

    /**
     * Возвращает версию Joomla! от 2 до 4 версии
     *
     * @return int
     */
    public function getJoomlaVersion()
    {
        return $this->joomlaVersion;
    }

    /**
     * Функция-обертка для getJsDate()
     *
     * @return mixed
     */
    public function getJsDate()
    {
        if ($this->joomlaVersion == 4) {
            return \JSHelper::getJsDate();
        }

        return getJsDate();
    }

    /**
     * Функция-обертка для получения инстанса \Joomla\Component\Jshopping\Site\Table\AddonTable
     *
     * @return mixed
     */
    public function getAddonTableObj()
    {
        if (JVERSION == 4) {
            $app = \JFactory::getApplication();

            /** @var MVCFactoryInterface $factory */
            $factory = $app->bootComponent('com_jshopping')->getMVCFactory();
            return $factory->createTable('addon', 'Site');
        }
        return \JTable::getInstance('addon', 'jshop');
    }

    /**
     * Возвращает составную часть имени файлов и директорий в зависимости от версии Joomla!
     * Необходимо для получения шаблонов формы для модуля в админ-панели для конкретной версии Joomla!
     *
     * @return string
     */
    public function getFilesVersionPostfix()
    {
        switch ($this->joomlaVersion) {
            case 2:
                return '2x';
            case 3:
                return '3x';
            default:
                return '';
        }
    }

    /**
     * Функция-обертка для регистрации обработчика события
     *
     * @param string $eventName - название события
     * @param array $listenerData - обработчик
     */
    public function registerEventListener($eventName, $listenerData)
    {
        switch ($this->joomlaVersion) {
            case 2:
            case 3:
                $dispatcher = \JDispatcher::getInstance();
                $dispatcher->register($eventName, $listenerData);
                break;
            default:
                \JFactory::getApplication()->getDispatcher()->addListener($eventName, $listenerData);
        }
    }

    /**
     * Функция-обертка для getSefLink()
     *
     * @param string $link
     * @return mixed
     */
    public function getSefLink($link)
    {
        if ($this->joomlaVersion == 4) {
            return \JSHelper::SEFLink($link);
        }

        return SEFLink($link);
    }
}