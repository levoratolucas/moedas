    <?php
    function renderTableFromApi($apiUrl, $selectedCurrency, $tableClass = '', $msg = 'Não há dados para exibir', $boldSecondColumn = false, $pageSize = 20, $pag = 'page')
    {
        $page = isset($_GET[$pag]) ? max(1, intval($_GET[$pag])) : 1;

        $json = file_get_contents($apiUrl);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p>Erro ao decodificar o JSON</p>";
            return;
        }

        if (empty($data)) {
            echo "<p>$msg</p>";
            return;
        }

        $totalRows = count($data);
        $totalPages = ceil($totalRows / $pageSize);

        $start = ($page - 1) * $pageSize;
        $data = array_slice($data, $start, $pageSize);

        $classAttribute = ($tableClass !== '') ? "class='$tableClass'" : '';


        $currencyName = $selectedCurrency;

        echo "<h1>Tabela de Dados para $currencyName</h1>"; // Título da tabela com o nome da moeda comparada

        echo "<div class='explanation'>";
        echo "<p><strong>Explicação:</strong> 1 Euro vale X Reais para compra e Y Reais para venda no mercado de câmbio.</p>";
        echo "</div>";

        echo "<table $classAttribute><tr>";

        // Traduzindo os cabeçalhos
        $headers = [
            'high' => 'Alta (R$)',
            'low' => 'Baixa (R$)',
            'varBid' => 'Variação (R$)',
            'pctChange' => 'Mudança (%)',
            'bid' => 'Compra (R$)',
            'ask' => 'Venda (R$)',
            'timestamp' => 'Data'
        ];

        foreach ($headers as $header) {
            echo "<th>$header</th>";
        }
        echo "</tr>";

        foreach ($data as $row) {
            echo "<tr>";
            foreach ($headers as $key => $header) {
                $value = isset($row[$key]) ? $row[$key] : '';
                if ($key == 'timestamp' || $key == 'create_date') {
                    // Ajustando o formato da data, verificando se o valor não está vazio
                    $value = !empty($value) ? date('d/m/Y', $value) : '';
                } elseif (is_numeric($value) && $key != 'pctChange') {
                    $value = number_format($value, 2, ',', '.');
                } elseif ($key == 'pctChange') {
                    $value = number_format($value, 2, ',', '.') . '%';
                }
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        echo "<div class='paginas'>";
        if ($page > 1) {
            $prevPage = $page - 1;
            echo "<a href='" . $_SERVER['PHP_SELF'] . "?currency=" . $_GET['currency'] . "&$pag=$prevPage'>Anterior</a>";
        }
        echo " $page ";
        if ($page < $totalPages) {
            $nextPage = $page + 1;
            echo "<a href='" . $_SERVER['PHP_SELF'] . "?currency=" . $_GET['currency'] . "&$pag=$nextPage'>Próximo</a>";
        }
        echo "</div>";
    }

    $currencies = ["USD-BRL", "EUR-BRL", "BTC-BRL"];
    $selectedCurrency = isset($_GET['currency']) ? $_GET['currency'] : 'USD-BRL';
    ?>

  

    <?php

    function generateChartFromApi($apiUrl, $chartId = 'chart', $chartType = 'line', $chartTitle = 'Gráfico', $xAxisLabel = 'Data', $yAxisLabel = 'Valor', $pageSize = 20)
    {
        $json = file_get_contents($apiUrl);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p>Erro ao decodificar o JSON</p>";
            return;
        }

        if (empty($data)) {
            echo "<p>Não há dados para exibir</p>";
            return;
        }

        $totalRows = count($data);
        $labels = [];
        $values = [];

        // Usar apenas um subconjunto de dados para o gráfico
        $data = array_slice($data, 0, $pageSize);

        foreach ($data as $row) {
            $labels[] = date('d/m/Y', $row['timestamp']); // Usar o timestamp como rótulo do eixo x
            $values[] = $row['bid']; // Usar o valor 'bid' para o eixo y (apenas como exemplo)
        }

        $labels = json_encode($labels);
        $values = json_encode($values);
    ?>

        <canvas id="<?= $chartId ?>"></canvas>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            var ctx = document.getElementById('<?= $chartId ?>').getContext('2d');
            var myChart = new Chart(ctx, {
                type: '<?= $chartType ?>',
                data: {
                    labels: <?= $labels ?>,
                    datasets: [{
                        label: '<?= $yAxisLabel ?>',
                        data: <?= $values ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // Cor de fundo do gráfico
                        borderColor: 'rgba(75, 192, 192, 1)', // Cor da linha do gráfico
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: '<?= $xAxisLabel ?>'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: '<?= $yAxisLabel ?>'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: '<?= $chartTitle ?>'
                        }
                    }
                }
            });
        </script>

    <?php
    }

    ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Tabela e Gráfico de Dados da API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        nav {
            margin-bottom: 20px;
        }

        nav ul {
            list-style-type: none;
            padding: 0;
        }

        nav ul li {
            display: inline;
            margin-right: 10px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        nav ul li a:hover {
            background-color: #555;
        }

        main {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        canvas {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .paginas {
            text-align: center;
            margin-top: 20px;
        }

        .paginas a {
            color: #007bff;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #ddd;
            margin: 0 4px;
            background-color: #fff;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }

        .paginas a:hover {
            background-color: #007bff;
            color: #fff;
        }

        .paginas a:active {
            background-color: #0056b3;
            color: #fff;
        }

    </style>
</head>

<body>

    <header>
        <h1>Tabela e Gráfico de Dados da API</h1>
        <nav>
            <ul>
                <?php
                $currencies = ["USD-BRL", "EUR-BRL", "BTC-BRL"];
                foreach ($currencies as $currency) {
                    echo "<li><a href='?currency=$currency'>$currency</a></li>";
                }
                ?>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container">
            <?php
            $selectedCurrency = isset($_GET['currency']) ? $_GET['currency'] : 'USD-BRL';
            $apiUrl = "https://economia.awesomeapi.com.br/json/daily/$selectedCurrency/1000";
            generateChartFromApi($apiUrl, 'myChart', 'line', 'Variação do Dólar Americano', 'Data', 'Valor em Reais');
            ?>
        </div>
        <div class="container">
            <?php
            $apiUrl = "https://economia.awesomeapi.com.br/json/daily/$selectedCurrency/1000";
            renderTableFromApi($apiUrl, $selectedCurrency, '', 'Não há dados para exibir');
            ?>
        </div>
    </main>

    <footer>
        <p>© <?php echo date("Y"); ?> Levoratech. Todos os direitos reservados.</p>
    </footer>

</body>

</html>
