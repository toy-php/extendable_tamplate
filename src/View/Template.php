<?php

namespace View;

use View\Interfaces\Template as TemplateInterface;
use View\Interfaces\Parser as ParserInterface;

class Template implements TemplateInterface
{

    protected $templateFile;
    protected $templateData;
    protected $functions = [];

    public function __construct($templateFile, $templateData = [])
    {
        $this->templateFile = $templateFile;
        $this->templateData = $templateData;
    }

    /**
     * Получить функцию ленивой инициализации шаблона
     * @param $templateFile
     * @param array $templateData
     * @return callable
     */
    static public function lazyLoad($templateFile, array $templateData = []): callable
    {
        return function ($file = null, $data = null) use ($templateFile, $templateData){
            $templateFile = $file ?: $templateFile;
            $templateData = $data ?: $templateData;
            return new Template($templateFile, $templateData);
        };
    }

    /**
     * Зарегистрировать функцию
     * @param string $name
     * @param callable $function
     * @throws Exception
     */
    public function registerFunction(string $name, callable $function)
    {
        if (isset($this->functions[$name])) {
            throw new Exception(sprintf('Функция с именем "%s" уже зарегистрирована', $name));
        }
        $this->functions[$name] = $function;
    }

    /**
     * Выполнить раннее зарегистрированную функцию
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->functions[$name])) {
            throw new Exception(sprintf('Функция с именем "%s" не зарегистрирована', $name));
        }
        return $this->functions[$name](...$arguments);
    }

    /**
     * Добавление данных к шаблону
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->templateData[$name] = $value;
    }

    /**
     * Получить экземпляр парсера
     * @return ParserInterface
     */
    public function makeParser(): ParserInterface
    {
        return new Parser($this);
    }

    /**
     * Рендеринг шаблона
     * @return string
     */
    public function __toString(): string
    {
        return $this->makeParser()->render($this->templateFile, $this->templateData);
    }

}