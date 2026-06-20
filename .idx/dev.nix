{pkgs}: {
  channel = "stable-24.05"; 

  packages = [
    pkgs.php82
    pkgs.php82Packages.composer
    pkgs.php82Extensions.pdo_mysql
    pkgs.nodejs_20
    pkgs.mariadb
  ];

  # NINGÚN SERVICIO NATIVO HABILITADO. CONTROL TOTAL EN ESPACIO DE USUARIO.

  idx = {
    extensions = [
      "google.gemini-cli-vscode-ide-companion"
    ];

    workspace = {
      onStart = {
        # 1. Inicializar estructura de base de datos en la carpeta del proyecto
        init-db = "if [ ! -d $(pwd)/.db ]; then mysql_install_db --datadir=$(pwd)/.db; fi";
        
        # 2. Iniciar el motor forzando TCP y un socket local
        start-db = "mysqld --datadir=$(pwd)/.db --port=3306 --bind-address=127.0.0.1 --socket=$(pwd)/.db/mysql.sock &";
      };
    };

    previews = {
      enable = true;
      previews = {
        web = {
          command = ["php" "artisan" "serve" "--port" "$PORT" "--host" "0.0.0.0"];
          manager = "web";
        };
      };
    };
  };
}