{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
  };

  outputs = { nixpkgs, ... }:
    let
      system = "x86_64-linux";
      pkgs = nixpkgs.legacyPackages.${system};

      common = [
        pkgs.git
        pkgs.fish
        pkgs.starship
        pkgs.just
        pkgs.gitflow
        pkgs.direnv
      ];

      php = pkgs.php85.withExtensions ({ enabled, all }:
        enabled ++ [
          all.pgsql
          all.pdo
          all.redis
          all.uuid
        ]);
    in
    {
      devShells.${system} = {
        # Ambiente completo (raiz do repo).
        # Ponto único de entrada para direnv e `just` — as receitas do justfile
        # chamam os comandos de backend/frontend via `cd`, então o shell root
        # precisa ter as ferramentas de ambos.
        default = pkgs.mkShell {
          packages = common ++ [

            # Backend
            php
            pkgs.phpantom-lsp
            pkgs.php85Packages.composer

            # Frontend
            pkgs.nodejs
            pkgs.pnpm
            pkgs.typescript
            pkgs.biome
            pkgs.emmet-language-server
          ];
        };

        # Ambiente focado no backend (usado pelo .envrc de ./backend)
        backend = pkgs.mkShell {
          packages = common ++ [
            pkgs.phpantom-lsp
            php
            pkgs.php85Packages.composer
          ];
        };

        # Ambiente focado no frontend (usado pelo .envrc de ./frontend)
        frontend = pkgs.mkShell {
          packages = common ++ [
            pkgs.nodejs
            pkgs.pnpm
            pkgs.typescript
            pkgs.biome
            pkgs.emmet-language-server
          ];
        };
      };
    };
}
