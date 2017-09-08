<?php

namespace View\Interfaces;

interface Template
{

    /**
     * Выполнить раннее зарегистрированную функцию
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments);

    /**
     * Получить экземпляр парсера
     * @return Parser
     */
    public function makeParser(): Parser;

    /**
     * Рендеринг шаблона
     * @return string
     */
    public function __toString(): string ;
}