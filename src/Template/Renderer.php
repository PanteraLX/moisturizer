<?php

namespace Moisturizer\Template;

interface Renderer
{
    public function render($template, $data = []);
}