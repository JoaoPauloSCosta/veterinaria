<?php

/**
 * Renderiza controles de paginação reutilizáveis
 * 
 * @param int $page Página atual
 * @param int $totalPages Total de páginas
 * @param int $limit Itens por página
 * @param int $total Total de registros
 * @param array $queryParams Parâmetros de query atuais
 * @return string HTML da paginação
 */
/**
 * Gera HTML de paginação acessível e responsivo
 * Inclui navegação, reticências e contagem exibida
 */
function render_pagination(int $page, int $totalPages, int $limit, int $total, array $queryParams = []): string {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Navegação de páginas" class="mt-3">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Botão Anterior
    if ($page > 1) {
        $prevParams = array_merge($queryParams, ['page' => $page - 1]);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?' . http_build_query($prevParams) . '">Anterior</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Anterior</span>';
        $html .= '</li>';
    }
    
    // Páginas
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);
    
    // Primeira página e reticências
    if ($startPage > 1) {
        $firstParams = array_merge($queryParams, ['page' => 1]);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?' . http_build_query($firstParams) . '">1</a>';
        $html .= '</li>';
        
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">...</span>';
            $html .= '</li>';
        }
    }
    
    // Páginas do intervalo
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pageParams = array_merge($queryParams, ['page' => $i]);
        $activeClass = $i == $page ? ' active' : '';
        $html .= '<li class="page-item' . $activeClass . '">';
        $html .= '<a class="page-link" href="?' . http_build_query($pageParams) . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Última página e reticências
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">...</span>';
            $html .= '</li>';
        }
        
        $lastParams = array_merge($queryParams, ['page' => $totalPages]);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?' . http_build_query($lastParams) . '">' . $totalPages . '</a>';
        $html .= '</li>';
    }
    
    // Botão Próximo
    if ($page < $totalPages) {
        $nextParams = array_merge($queryParams, ['page' => $page + 1]);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="?' . http_build_query($nextParams) . '">Próximo</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Próximo</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    // Informações da paginação
    $showingCount = min($limit, $total - (($page - 1) * $limit));
    $html .= '<div class="text-center text-muted small mt-2">';
    $html .= 'Mostrando ' . $showingCount . ' de ' . $total . ' registros ';
    $html .= '(Página ' . $page . ' de ' . $totalPages . ')';
    $html .= '</div>';
    
    return $html;
}

/**
 * Calcula informações de paginação
 * 
 * @param int $page Página atual
 * @param int $limit Itens por página
 * @param int $total Total de registros
 * @return array [page, limit, offset, totalPages]
 */
/**
 * Calcula página, offset e total de páginas
 * Garante limites mínimos e arredonda corretamente
 */
function calculate_pagination(int $page, int $limit, int $total): array {
    $page = max(1, $page);
    $limit = max(1, $limit);
    $offset = ($page - 1) * $limit;
    $totalPages = ceil($total / $limit);
    
    return [
        'page' => $page,
        'limit' => $limit,
        'offset' => $offset,
        'totalPages' => $totalPages
    ];
}