<?php

namespace App\Service;

use Dompdf\Dompdf;

class ArreteGenerator
{
    public function generate(
        string $html,
        string $path
    ): void {

        $dompdf = new Dompdf();

        $dompdf->loadHtml($html);

        $dompdf->render();

        file_put_contents(
            $path,
            $dompdf->output()
        );
    }
}