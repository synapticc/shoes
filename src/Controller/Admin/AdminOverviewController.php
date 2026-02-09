<?php

// src/Controller/Admin/AdminOverviewController.php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface as Chart;
use Symfony\UX\Chartjs\Model\Chart as Type;

/**
 * Display Admin Overview page.
 */
class AdminOverviewController extends AbstractController
{
    /**
     * Display charts and metrics tables on the Admin Overview page.
     */
    public function index(Chart $chart): Response
    {
        $chart = $chart->createChart(Type::TYPE_LINE);
        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',  'October', 'November', 'December'],
            'datasets' => [
                [
                    'label' => 'Yearly Sales (2024)',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 100000, 200000, 300000, 400000, 500000, 600000],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 1000000,
                ],
            ],
        ]);

        return $this->render('admin/8_overview/index.html.twig', [
            'controller_name' => 'AdminOverviewController',
            'chart' => $chart,
        ]);
    }
}
