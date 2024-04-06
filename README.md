# CLASSES DE CRIAÇÃO E GERENCIAMENTO DE ROTAS DE API COM RECURSOS BÁSICOS

Completamente standalone (sem bibliotecas de terceiros), com classes com recursos básicos de Request e Response, para criação e gerenciamento de rotas;

## Classes
- RequestController: Classe para gerenciamento de requisições; Com métodos para obter dados da requisição, como parâmetros, corpo, cabeçalhos, etc.
- ResponseController: Classe para gerenciamento de respostas; Com métodos para enviar respostas, como JSON, texto, erro, etc.
- RoutesController: Classe para criação e gerenciamento de rotas; Com métodos para adicionar rotas, grupos, middlewares, etc.
- autoload.php: Arquivo de autoload das classes.

Com o redirecionamento de todas as requisições para o arquivo index.php, é possível criar rotas de API com recursos básicos, pequenas respostas html, json, etc.