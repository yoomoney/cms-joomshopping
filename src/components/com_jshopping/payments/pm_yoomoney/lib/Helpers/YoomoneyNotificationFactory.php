<?php

namespace YooMoney\Helpers;

use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\Notification\AbstractNotification;

/**
 * Класс-фабрика для получения объекта уведомления от Юkassa
 */
class YoomoneyNotificationFactory
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var AbstractNotification
     */
    private $notificationObject;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Возвращает объект уведомления от Юkassa, если он был получен ранее, или сначала
     * преобразует уведомление от Юkassa в объект
     *
     * @return AbstractNotification
     * @throws \Exception
     */
    public function getNotificationObject()
    {
        if ($this->notificationObject) {
            return $this->notificationObject;
        }

        $source = file_get_contents('php://input');
        $this->logger->log('debug', 'Notification body source: '.$source);
        if (empty($source)) {
            $this->logger->log('debug', 'Notification error: body is empty!');
            header('HTTP/1.1 400 Body is empty');
            die();
        }

        $data = json_decode($source, true);
        if (empty($data)) {
            $this->logger->log('debug', 'Notification error: invalid body!');
            header('HTTP/1.1 400 Invalid body');
            die();
        }

        $factory = new NotificationFactory();
        $notification = $factory->factory($data);
        $this->setNotificationObj($notification);

        return $notification;
    }

    /**
     * Сеттер для объекта уведомления
     *
     * @param $object
     */
    private function setNotificationObj($object)
    {
        $this->notificationObject = $object;
    }
}