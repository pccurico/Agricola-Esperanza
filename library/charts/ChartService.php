<?php

declare(strict_types=1);

namespace CampoSur\Library\Charts;

final class ChartService
{
    public function buildScript(string $selector, array $config): string
    {
        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return "const chart = new Chart(document.querySelector(\"{$selector}\"), {$json});";
    }
}
