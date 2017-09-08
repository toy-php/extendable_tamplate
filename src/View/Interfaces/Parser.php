<?php

namespace View\Interfaces;

interface Parser
{

    /**
     * Рендеринг шаблона
     * @param $templateFile
     * @param array $templateData
     * @return string
     */
    public function render($templateFile, array $templateData = []): string;
}