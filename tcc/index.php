<?php
// search.php: Aqui você pode fazer o processamento da pesquisa, por exemplo, buscar no banco de dados ou em um arquivo.
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['q'])) {
    $searchQuery = htmlspecialchars($_GET['q']);
    // A lógica de busca real seria aqui, talvez conectando ao banco de dados ou realizando a pesquisa em arquivos.
    // Exemplo de resposta (apenas para fins de demonstração):
    echo "<h2>Resultados para: " . $searchQuery . "</h2>";
    // Coloque a lógica de pesquisa real abaixo
    // Ex: Buscar dados no banco ou no arquivo
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Bonfire Games</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Fundo com o gif da fogueira */
        body {
            height: 100vh;
            background: url('img/inicial.gif') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
            padding-top: 80px; /* espaço para o menu */
        }

        /* Menu fixo no topo */
        .top-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(0,0,0,0.9);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.7);
            z-index: 1000;
        }

        /* Logo no menu */
        .menu-logo {
            font-size: 2rem;
            font-weight: 900;
            color: #ff6600; /* cor laranja para destacar */
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Menu de categorias */
        .menu-nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
        }

        .menu-nav li a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .menu-nav li a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        /* Container principal para centralizar logo e barra */
        .main-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
        }

        /* Logo grande */
        .logo {
            font-size: 6rem;
            font-weight: 900;
            margin-bottom: 40px;
            user-select: none;
        }

        /* Container da barra de pesquisa */
        .search-container {
            width: 100%;
            max-width: 600px;
        }

        /* Estilo da barra de pesquisa estilo Google */
        .search-container form {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 24px;
            background-color: rgba(255,255,255,0.9);
            padding: 8px 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .search-container input[type="text"] {
            flex: 1;
            border: none;
            outline: none;
            font-size: 1.2rem;
            padding: 8px 12px;
            border-radius: 24px;
            font-weight: 500;
        }

        .search-container button {
            background-color: #4285F4;
            border: none;
            color: white;
            font-weight: 600;
            padding: 8px 20px;
            margin-left: 10px;
            border-radius: 24px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #357ae8;
        }

        /* Responsividade */
        @media (max-width: 640px) {
            .top-menu {
                padding: 10px 20px;
            }
            .menu-logo {
                font-size: 1.5rem;
            }
            .menu-nav ul {
                gap: 15px;
            }
            .menu-nav li a {
                font-size: 0.9rem;
                padding: 8px 12px;
            }
            .logo {
                font-size: 4rem;
                margin-bottom: 30px;
            }
            .search-container {



                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="top-menu">
        <div class="menu-logo">BONFIRE GAMES</div>
        <nav class="menu-nav">
            <ul>
                <li><a href="index.php">Firelink</a></li>
                <li><a href="#">Fansgames</a></li>
                <li><a href="forum.php">Bonfires</a></li>
                <li><a href="#">Sobre</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="logo">BONFIRE GAMES</div>
        <div class="search-container">
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="Bear. Seek. Seek. Lest." autocomplete="off" required />
                <button type="submit">Buscar</button>
            </form>
        </div>
    </div>
</body>
</html>
