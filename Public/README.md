# Observatório Mulheres na Computação

Este diretório contém um protótipo do site do **Observatório Mulheres na Computação**.  O objetivo do projeto é
reunir e disponibilizar informações sobre mulheres que contribuíram ou
contribuem para a área de Tecnologia da Informação, a partir de dados
públicos (como Wikipédia e Wikidata) e estudos acadêmicos presentes na
documentação do projeto.  A estrutura de pastas e o código aqui
implementados foram desenhados para respeitar as práticas descritas
nas diagramas de caso de uso e de classes fornecidos, além de manter
os arquivos sensíveis fora da raiz pública do servidor.

## Estrutura de diretórios

```
observatorio-site/
├── config/              # Arquivos de configuração (não expostos ao público)
│   └── config.php       # Configurações básicas da aplicação
├── public/              # Raiz pública do servidor web
│   ├── index.php        # Página inicial com dashboard interativo
│   ├── api/             # Endpoints em JSON para consumo via JS
│   │   ├── perfis.php      # Lista perfis com filtros e paginação
│   │   └── estatisticas.php # Estatísticas agregadas (totais, continentes, etc.)
│   ├── css/             # Folhas de estilo
│   │   └── style.css    # Estilos modernos inspirados na interface de referência
│   ├── js/              # Scripts de interface
│   │   └── main.js      # Lógica de busca, filtros e gráficos (Chart.js)
│   │   └── continent.js # Lógica da página de continente específica
│   ├── images/          # Imagens públicas
│   └── .htaccess        # Regras do Apache para proteger pastas sensíveis

├── public/continente.php   # Página de detalhes para cada continente (acessível via rotas amigáveis)
├── src/                 # Código de aplicação PHP
│   ├── Core/            # Classes principais (controladores e app)
│   │   ├── ObservatorioApp.php
│   │   └── ErroHandler.php
│   ├── Model/           # Modelos de dados
│   │   ├── Perfil.php
│   │   ├── Pais.php
│   │   ├── Conquista.php
│   │   ├── Continente.php
│   │   ├── Coordenadas.php
│   │   └── Estatisticas.php
│   ├── Service/         # Serviços e utilitários
│   │   ├── WikipediaClient.php
│   │   ├── WikidataClient.php
│   │   ├── RepositorioCache.php
│   │   ├── Filtro.php
│   │   └── RateLimiter.php
│   └── View/            # Classes de apresentação
│       ├── ListaCartoesVisao.php
│       ├── MapaVisao.php
│       ├── GraficosVisao.php
│       └── Dashboard.php
└── composer.json        # (Opcional) dependências do Composer
```

## Segurança e boas práticas

O conteúdo sensível (código PHP, configuração e cache) reside fora da
pasta `public/`.  Somente arquivos que precisam ser servidos pelo
servidor web (como `index.php`, imagens e CSS) ficam na raiz pública.
O arquivo `.htaccess` em `public/` pode ser usado em servidores
Apache para negar acesso às pastas internas, caso a aplicação seja
implantada em um ambiente que utilize `.htaccess`.

## Observações

* As classes e métodos implementados são esboços que seguem o
  diagrama de classes fornecido.  Comentários `TODO` foram
  adicionados nos pontos onde integrações reais com APIs públicas
  (Wikipédia/Wikidata) devem ser implementadas.
* O diretório `data/` contém um arquivo `perfis.json` com uma
  coleção de cem perfis de mulheres em tecnologia gerados para fins
  demonstrativos.  Esse dataset é carregado no `ObservatorioApp` e
  também pelas rotas JSON (`/api/perfis.php` e `/api/estatisticas.php`).
  Em uma implementação real, esse arquivo deve ser substituído por
  dados provenientes de fontes oficiais ou consultas dinâmicas.
* Para ampliar o projeto, recomenda‑se configurar o Composer para
  autoload (`composer dump-autoload`) e acrescentar testes.
* O site utiliza PHP orientado a objetos para separar responsabilidades
  (modelos, serviços, visão e controle), facilitando a manutenção e
  evolução.
