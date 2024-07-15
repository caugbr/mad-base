# Mad-base
Um boilerplate para o Mad Builder com algumas funcionalidades adicionadas.

## Novas classes
### Options
Criamos uma tabela para guardar parâmetros de configuração do sistema (model/Options.php) que, em conjunto com os controllers Config e OptionsForm, fornece a estrutura pra gerenciar esses parâmetros, tanto ao nível do usuário quanto no nível das classes do sistema.

### Actions
Como o projeto já vem pronto é extremamente comum termos que adicionar trechos de código a arquivos já existentes e isso causa uma confusão deixando seu código espalhado, emfim, eu não gostei muito disso. Com a classe Actions podemos inserir "ganchos" nessas classes, e através dessa única linha inserida, abrimos uma porta pra executar qualquer código ou modificar variáveis.

### Roles
O projeto original tem um sistema de permissões interessante, que pode ser bem específico, mas exige que façamos uma configuração chatinha a cada usuário criado, definindo a que grupos ele tem acesso, que "programas" e pode usar e qual será sua página inicial. Nós introduzimos o conceito de níveis de usuário, onde podemos criar vários níveis, cada um com seu set específico de permissões, grupos e página inicial, bastando definir o nível ao criar ou editar um usuário.

### MenuManager
Existe um editor de menu no grupo Administração que já vem no projeto, mas ele não permitia mudar um sub item de grupo. Criei uma versão visualmente mais amigável pra editar o menu que permite mudar itens de grupo. Se você usa o editor online, inicialmente o Mad builder adiciona novas páginas ao menu automaticamente. Se usar esse sistema isso vai parar e você terá que incluir novos itens manualmente.

## Javascript
Algumas funcionalidades e utilidades em Javascript foram adicionadas.

#### Menu
Tentamos marcar o item atual (e abrir o submenu relativo) com base na URL carregada.

#### Tema
Adicionamos uma identificação do tema carregado como atributo do BODY (data-theme: builder | lte2 | lte3 | bsb).

#### Side panel
Adicionamos uma rotina para fechar o painel lateral com Esc ou clicando fora.

#### adiantiHelper
Combinamos várias funcionalidades para interagir com o sistema Adianti a partir do javascript.
- **Interação com o usuário**
  As caixas de diálogo do sistema, montadas como Promises: `alert`, `errorAlert`, `prompt`, `confirm` e `toast`
- **Carregar conteúdo**
  Funções para buscar conteúdos no servidor e exibir sem recarregar a página: `loadPage`, `loadSidePanel`, `loadHtml`
- **Rest API**
  Fornece um meio simples de acessar a API do sistema, com base nos nomes da classe e do método, como funcionam as URLs do sistema. Você deve definir sua `restKey` em `adiantiHelper.js`.
- **actions**
  Similar às actions criadas no nível do PHP. Com os métodos aninhados em `actions` podemos definir ganchos no nosso código Javascript, facilitando o trabalho de interagir em pontos específicos do código. Há o método actions.graft que serve para enxertar ganchos em funções já existentes. Usando o `graft`, já criamos os hooks 'loadPage', 'openSidePanel' e 'closeSidePanel'.
