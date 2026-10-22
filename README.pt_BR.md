# Plugin Toggle Required Metadata

[English](README.md) · **Português (Brasil)** · [Español](README.es.md)

Este plugin permite que uma revista, editora ou servidor exija dos contribuidores de uma submissão mais do que a aplicação exige por conta própria: a **Afiliação**, o **Resumo da Biografia** e o **ORCID iD** podem ser tornados obrigatórios, cada um separadamente.

Cada exigência é verificada na conclusão da submissão, de modo que nenhuma submissão passa sem ela. As três também são marcadas como obrigatórias no formulário de Contribuidor, onde o autor as preenche.

A exigência de ORCID é de outra natureza, porque desde a 3.5 ninguém digita um ORCID iD: ele só é registrado quando o contribuidor o autoriza pelo e-mail de verificação. Por isso o que o plugin exige é que **pelo menos um contribuidor tenha o ORCID iD verificado** e que a **verificação tenha sido solicitada a todos os demais**. Na última etapa da submissão isso aparece como um bloco único, que conta quantos contribuidores já verificaram e diz em que ponto cada um está, para que o autor distinga o que ainda depende dele do que apenas aguarda outra pessoa.

![A exigência de ORCID na última etapa de uma submissão](/assets/review-orcid-requirement.png)

## Compatibilidade

Este plugin é compatível com as seguintes aplicações da PKP:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Linhas de versão anteriores são atendidas por outros branches deste plugin: `stable-3_3_0` (OJS, OPS e OMP 3.3) e `stable-3_4_0` (OJS, OPS e OMP 3.4).

## Instalação

Instale o plugin **Toggle Required Metadata** pela **Galeria de Plugins**, em **Painel de Controle > Website > Plugins**

## Configurações

Habilitar o plugin não exige nada por si só: cada exigência fica desligada até ser escolhida. As configurações do plugin, acessadas em **Painel de Controle > Website > Plugins**, são onde se escolhe quais delas uma submissão precisa cumprir:

![As configurações do plugin com as três exigências habilitadas](/assets/settings-orcid-enabled.png)

A opção ORCID só fica disponível enquanto o ORCID estiver habilitado nas configurações de ORCID da aplicação. Quando não estiver, a opção aparece desabilitada, junto de uma nota explicando o motivo, e a escolha salva é preservada:

![As configurações do plugin com o ORCID não habilitado na aplicação](/assets/settings-orcid-disabled.png)

## Onde cada exigência é aplicada

| Opção | Formulário de Contribuidor | Conclusão da submissão |
|---|---|---|
| Afiliação | marcada como obrigatória | bloqueada até que todo contribuidor tenha ao menos uma afiliação |
| Resumo da Biografia | marcado como obrigatório; o formulário não pode ser salvo sem ele | bloqueada até que todo contribuidor tenha um |
| ORCID | marcado como obrigatório ao lado do botão de verificação; não editável manualmente na 3.5 | bloqueada até que um contribuidor tenha o iD verificado e a verificação tenha sido solicitada a todos os demais, exibido como um bloco único com a situação de cada contribuidor |

### No formulário de Contribuidor

Os metadados selecionados são marcados como obrigatórios onde o autor os preenche. O ORCID iD é marcado ao lado do botão de verificação, que na 3.5 é o único caminho pelo qual um contribuidor pode fornecer um:

![O campo ORCID do formulário de Contribuidor, marcado como obrigatório](/assets/contributor-form-orcid-required.png)

![O mesmo campo depois que a verificação foi solicitada](/assets/contributor-form-orcid-requested.png)

### Na conclusão da submissão

Toda exigência é verificada de novo na última etapa, e a submissão não pode ser enviada até que todas sejam cumpridas. Os avisos ficam agrupados no topo do painel de contribuidores, ao lado do botão Editar que os resolve:

![A última etapa de uma submissão com as três exigências não cumpridas](/assets/review-all-requirements.png)

### O que a verificação não cobre

As exigências são sobre o que chega pelo formulário de submissão, e é aí que valem: são verificadas quando o autor conclui a submissão, e em nenhum outro lugar. Contribuidor editado depois no fluxo de trabalho, submissão criada pela API REST e tudo que entra por importação ou pelo QuickSubmit não passam pela verificação, nem a publicação passa. O que o plugin guarda é a porta: nenhuma submissão é *recebida* sem os metadados, o que não é o mesmo que nenhuma submissão da revista ficar sem eles.

## Com a submissão já no fluxo de trabalho

Depois de submetido, a exigência de ORCID ainda pode não estar cumprida. Enquanto for assim, e a submissão ainda estiver em andamento, a aba de contribuidores do fluxo de trabalho diz quem a está segurando, separando os contribuidores sem pedido de verificação pendente, os que têm salvo um iD não verificado, que precisa ser excluído antes de a verificação poder ser solicitada, os que receberam o e-mail de verificação e não o abriram, os que recusaram a autorização no ORCID e os que ficaram sem link de verificação pendente, com a orientação que cabe a quem lê — um editor pode solicitar ou reenviar a verificação, um autor quase só pode cobrar o contribuidor. O aviso não aparece numa submissão que não está mais em andamento — rejeitada, agendada ou publicada, incluindo as novas versões de uma publicada e tudo o que foi publicado antes de o plugin ser habilitado — nem depois que um contribuidor verificou o seu iD e a verificação foi solicitada a todos os outros:

![O aviso de ORCID como o editor o vê](/assets/workflow-warning-editor.png)

![O aviso de ORCID como o autor o vê](/assets/workflow-warning-author.png)

## Notas sobre a 3.5

* **Não é possível exigir que o contribuidor digite um ORCID iD.** Desde a 3.5 a aplicação recusa qualquer ORCID iD enviado pelo formulário de Contribuidor; um iD só é registrado pelo fluxo de verificação do ORCID. Versões anteriores deste plugin exigiam o iD digitado sempre que o plugin ORCID Profile estivesse desligado, e isso não é mais possível.
* **A opção ORCID acompanha as configurações de ORCID da aplicação.** Com o ORCID desabilitado a opção não surte efeito, e a escolha salva é preservada e volta a valer assim que o ORCID for habilitado. Note que o ORCID também pode ser habilitado para toda a instalação, no nível do site, o que liga a exigência para todas as revistas, editoras ou servidores de uma vez.
* **A afiliação e o ORCID iD são marcados no formulário de Contribuidor, mas verificados na conclusão da submissão.** Os dois campos renderizam o próprio rótulo, então o plugin acrescenta um elemento ao lado de cada um para carregar a marca de obrigatoriedade. Nenhum dos dois é sinalizado como obrigatório para o próprio formulário: nenhum deles limpa um erro depois de levantado, o que deixaria o formulário de Contribuidor impossível de salvar.

## Compilação dos assets de front-end

As marcas de obrigatoriedade nos campos de afiliação e ORCID, o bloco de ORCID na última etapa da submissão e o aviso de ORCID no fluxo de trabalho são componentes Vue, compilados com o Vite. O resultado da compilação é versionado em `public/build/`, de modo que instalar o plugin não exige nenhum passo de build. Para recompilar após alterar qualquer coisa em `resources/`:

```
npm install
npm run build
```

# Licença
__Este plugin é licenciado sob a GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
