{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
  };

  outputs = { nixpkgs, ... }:
    let
      system = "x86_64-linux";
      pkgs = nixpkgs.legacyPackages.${system};

      php = pkgs.php85.withExtensions ({ enabled, all }:
        enabled ++ [
          all.pgsql
          all.pdo
          all.redis
          all.uuid
        ]);
    in
    {
      devShells.${system}.default = pkgs.mkShell {
        packages = [
          # Tools
          pkgs.git
          pkgs.phpantom-lsp
          pkgs.fish
          pkgs.starship

          # Backend
          php
          pkgs.php85Packages.composer

          # Frontend
          pkgs.nodejs
          pkgs.pnpm
          pkgs.typescript
          pkgs.biome
        ];


      };
    };
}
