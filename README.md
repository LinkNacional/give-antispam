# give-antispam

O Give-Antispam é um plugin para a plataforma de doação GiveWP que tem o objetivo de fazer a proteção de formulários de doação contra teste de cartões e doações spam.

## Instalação

1) Procure na barra lateral a área de plugins do Wordpress;

2) Em plugins instalados procure pela opção 'adicionar novo';

3) Clique na opção de 'enviar plugin' no título da página e faça o upload do plugin give-antispam.zip;

4) Clique no botão 'instalar agora' e depois ative o plugin instalado;

5) Agora vá para o menu de configurações do GiveWP;

6) Selecione a opção 'controle de acesso';

7) Procure pela opção 'Habilitar proteção de doações spam';

8) Clique em salvar;

9) Novas opções irão aparecer, pode deixar os valores padrões ou alterar para algo que se encaixe com suas necessidades;

10) Clique em salvar;

11) Caso tenha as chaves de acesso do Recaptcha V3 e deseje habilitá-lo, procura pela opção "Habilitar Recaptcha" clique em "habilitado";

12) Clique em salvar;

13) Novos campos aparecerão, agora insira suas credenciais do Recaptcha V3;

14) Clique em salvar;

Pronto, o plugin do Give-Antispam está ativo e em funcionamento.

## Changelog

### v1.1.2
- Corrigido bug de notice do Google Recaptcha para formulários de valor fixo;
- Ajuste na lógica para deletar logs antigos;
- Ajuste nos logs do antispam.

### v1.1.1
- Agora existe um link na descrição das configurações que redireciona para o administrativo do Recaptcha V3;
- Ajustado título da configuração do Recaptcha.

### v1.1.0
- Agora o give antispam tem a opção de ativar e configurar o Recaptcha V3;
- Ajustes e correções nas configurações do plugin;
- Ajustes de formatação das configurações.

### v1.0.0
- Plugin com funcionalidade de verificar os IPs das doações e caso sejam iguais bloqueia tentativas de pagamento.