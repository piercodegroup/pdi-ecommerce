# 📌 Padoca Dona Inês — Sistema Web de Delivery e Gestão de Padaria

![Status](https://img.shields.io/badge/Status-Concluído-brightgreen)

O **Padoca Dona Inês** é um sistema web completo para uma padaria com vendas via delivery e painel administrativo.  
Foi desenvolvido como **Projeto de Desenvolvimento Integrador (PDI)** com o objetivo de modernizar a operação e oferecer uma melhor experiência aos clientes.

---

## 🥖 Sobre o Projeto

A aplicação contempla rotinas de **gestão interna** para administradores e **compras online** para clientes, permitindo:

✔ Venda de produtos online  
✔ Controle de pedidos e entregas  
✔ Cadastro de clientes, endereços e cartões  
✔ Programa de fidelidade  
✔ Relatórios para tomada de decisão

O sistema opera com **dois perfis de usuários**:

| Perfil | Recursos |
|--------|----------|
| 👨‍💼 Administrador | Gestão completa do negócio |
| 🛒 Cliente | Compra rápida, prática e com fidelidade |

---

## ✨ Funcionalidades

### 🎯 Administrador
- Gestão de Usuários (CRUD + níveis de acesso)
- Gestão de Categorias e Produtos (CRUD com imagens)
- Gestão de Clientes com histórico de pedidos
- Gestão de Pedidos (status: pendente → em preparo → a caminho → entregue)
- Relatórios e estatísticas:
  - Produtos mais vendidos
  - Vendas por período
  - Fluxo de clientes fidelizados

### 🛍️ Cliente
- Cadastro/Login (incluindo 🔐 Google OAuth)
- Catálogo de produtos com imagens e preços
- Carrinho e confirmação de pedido com entrega
- Cadastro de cartão e endereço
- Acompanhamento do status do pedido
- Programa de fidelidade com acúmulo e uso de pontos
- Máscara automática de CEP

---


| Camada         | Tecnologia                      |
| -------------- | ------------------------------- |
| Linguagem      | PHP 8+                          |
| Framework      | Laravel                         |
| Banco de Dados | MySQL                           |
| Front-end      | HTML, TailwindCSS, JavaScript   |
| Autenticação   | Google OAuth                    |
| Ferramentas    | Composer, Artisan, MySQL Server |


```md

⚙️ Instalação e Execução

# Clonar o repositório
git clone https://github.com/seuusuario/padoca-dona-ines.git

# Acessar pasta
cd padoca-dona-ines

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Criar banco e migrar tabelas
php artisan migrate --seed

# Iniciar servidor
php artisan serve

Acesse no navegador:
➡ http://localhost:8000/
