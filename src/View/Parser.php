<?php

namespace View;

use View\Interfaces\Template as TemplateInterface;
use View\Interfaces\Parser as ParserInterface;

class Parser implements ParserInterface
{

    /**
     * @var TemplateInterface
     */
    protected $template;

    /**
     * Модель представления
     * @var array
     */
    protected $data = [];

    /**
     * Имя макета шаблона
     * @var string
     */
    protected $layoutFile = '';

    /**
     * Модель макета шаблона
     * @var array
     */
    protected $layoutData = [];

    /**
     * Секции
     * @var array
     */
    protected $sections = [];

    public function __construct(TemplateInterface $template)
    {
        $this->template = $template;
    }

    /**
     * Получить секции шаблона
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Установить секции
     * @param array $sections
     */
    public function setSections(array $sections)
    {
        $this->sections = $sections;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $value = $this->__isset($name) ? $this->data[$name] : null;
        if(is_callable($value)){
            return $this->data[$name] = $value();
        }
        return $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        return $this->template->__call($name, $arguments);
    }

    /**
     * Старт секции
     * @param string $name
     * @throws Exception
     */
    public function start($name)
    {
        if ($name === 'content') {
            throw new Exception('Секция с именем "content" зарезервированна.');
        }
        $this->sections[$name] = null;
        ob_start(null, 0,
            PHP_OUTPUT_HANDLER_CLEANABLE |
            PHP_OUTPUT_HANDLER_FLUSHABLE |
            PHP_OUTPUT_HANDLER_REMOVABLE
        );
    }

    /**
     * Стоп секции
     * @throws Exception
     */
    public function stop()
    {
        if (empty($this->sections)) {
            throw new Exception('Сперва нужно стартовать секцию методом start()');

        }
        end($this->sections);
        $this->sections[key($this->sections)] = ob_get_contents();
        ob_end_clean();
    }

    /**
     * Вывод секции
     * @param string $name
     * @return string|null;
     */
    public function section($name)
    {
        return isset($this->sections[$name]) ? $this->sections[$name] : null;
    }

    /**
     * Объявление макета шаблона
     * @param $layoutFile
     * @param array $layoutData
     */
    public function layout($layoutFile, array $layoutData = [])
    {
        $this->layoutFile = $layoutFile;
        $this->layoutData = array_merge($this->data, $layoutData);
    }

    /**
     * Вставка представления в текущий шаблон
     * @param $templateFile
     * @param array $templateData
     * @return string
     */
    public function insert($templateFile, array $templateData = [])
    {
        /** @var Parser $parser */
        $parser = $this->template->makeParser();
        $result = $parser->render($templateFile, $templateData ?: $this->data);
        $this->sections = array_merge($this->sections, $parser->getSections());
        return $result;
    }

    /**
     * Загрузка шаблона
     * @param $templateFile
     * @throws Exception
     */
    protected function loadTemplateFile($templateFile)
    {
        if (!file_exists($templateFile)) {
            throw new Exception(sprintf('Файл шаблона по пути "%s" недоступен', $templateFile));
        }
        include $templateFile;
    }

    /**
     * @inheritdoc
     */
    public function render($templateFile, array $templateData = []): string
    {
        try {
            ob_start(null, 0,
                PHP_OUTPUT_HANDLER_CLEANABLE |
                PHP_OUTPUT_HANDLER_FLUSHABLE |
                PHP_OUTPUT_HANDLER_REMOVABLE
            );
            $this->data = $templateData;
            $this->loadTemplateFile($templateFile);
            $content = ob_get_contents();
            ob_end_clean();
            if (!empty($this->layoutFile)) {
                /** @var Parser $layout */
                $layout = $this->template->makeParser();
                $layout->setSections(array_merge($this->sections, ['content' => $content]));
                $content = $layout->render($this->layoutFile, $this->layoutData);
            }
            return $content;
        } catch (Exception $e) {
            if (ob_get_length() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
    }

}