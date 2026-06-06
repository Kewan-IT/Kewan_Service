<?php
namespace App\Controllers;

use App\Models\Dashboard;
use App\Middleware\AuthMiddleware;
use Core\View;

class DashboardController
{
    private Dashboard $model;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->model = new Dashboard();
    }

    public function index(): void
    {
        $kpis              = $this->model->kpisDia();
        $topProdutos       = $this->model->topProdutosMes(10);
        $pagamentos        = $this->model->vendasPorPagamento();
        $categorias        = $this->model->vendasPorCategoria();
        $melhorFunc        = $this->model->melhorFuncionarioMes();
        $rankingFunc       = $this->model->rankingFuncionariosMes(5);
        $aniversariantes   = $this->model->aniversariantesMes();
        $stockCritico      = $this->model->produtosStockCritico(8);
        $lotesAVencer      = $this->model->lotesAVencer(60, 8);
        $ultimasVendas     = $this->model->ultimasVendas(8);
        $receitaMeses      = $this->model->receitaUltimosMeses(6);
        $vendas30          = $this->model->vendasUltimos30Dias();
        $vendasHora        = $this->model->vendasPorHoraHoje();
        $resultadoMes      = $this->model->resultadoMes();
        $caixaHoje         = $this->model->resumoCaixaHoje();

        View::render('dashboard.index', [
            'titulo'           => 'Dashboard',
            'activePage'       => 'dashboard',
            'kpis'             => $kpis,
            'topProdutos'      => $topProdutos,
            'pagamentos'       => $pagamentos,
            'categorias'       => $categorias,
            'melhorFunc'       => $melhorFunc,
            'rankingFunc'      => $rankingFunc,
            'aniversariantes'  => $aniversariantes,
            'stockCritico'     => $stockCritico,
            'lotesAVencer'     => $lotesAVencer,
            'ultimasVendas'    => $ultimasVendas,
            'receitaMeses'     => $receitaMeses,
            'vendas30'         => $vendas30,
            'vendasHora'       => $vendasHora,
            'resultadoMes'     => $resultadoMes,
            'caixaHoje'        => $caixaHoje,
        ]);
    }

    public function resumoAjax(): void
    {
        $kpis = $this->model->kpisDia();
        View::json($kpis);
    }
}
