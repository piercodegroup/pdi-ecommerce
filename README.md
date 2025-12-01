# PDI - E-commerce de Padaria

Repo: https://github.com/piercodegroup/pdi-ecommerce

## Como rodar localmente (Docker)
1. Copie o arquivo `.env.example` para `.env` e ajuste as variáveis do DB.
2. Build e up: docker-compose up -d --build

3. Acesse: http://localhost:8080

## Pipeline CI/CD
Arquivo: `Jenkinsfile` na raiz. Pipeline cria imagem Docker e faz push para Docker Hub, depois faz deploy via docker-compose.

