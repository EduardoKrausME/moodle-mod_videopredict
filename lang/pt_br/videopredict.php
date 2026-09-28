<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings.
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addpredictionpoint'] = 'Adicionar ponto de previsão';
$string['answer'] = 'Resposta';
$string['answered'] = 'Respondido';
$string['awardedpoints'] = 'Pontos atribuídos';
$string['completiondetail:percent'] = 'Watch at least {$a}% of the video';
$string['completiondetail:predictions'] = 'Make all mandatory predictions';
$string['completionpercent'] = 'Require watched percentage';
$string['completionpredictions'] = 'Require all mandatory predictions';
$string['continuevideo'] = 'Continuar vídeo';
$string['correct'] = 'Correta';
$string['correctanswer'] = 'Correct option value';
$string['correctanswer_help'] = 'Enter the option key, for example A, 1, true or false. Leave blank when there is no objectively correct prediction.';
$string['correctanswers'] = 'Acertos';
$string['correctness'] = 'Correção';
$string['exportcsv'] = 'Exportar CSV';
$string['gradeheader'] = 'Grade';
$string['grademode'] = 'Grade calculation';
$string['grademodeblended'] = '50% prediction score + 50% watched percentage';
$string['grademodepredictions'] = 'Prediction score';
$string['grademodeprogress'] = 'Watched percentage';
$string['gradeprediction'] = 'Avaliar previsão';
$string['gradesaved'] = 'Nota salva.';
$string['incorrect'] = 'Incorreta';
$string['invalidgrade'] = 'Grade/points cannot be negative.';
$string['invalidpercent'] = 'Enter a percentage from 0 to 100.';
$string['invalidresponse'] = 'The selected response is not valid for this prediction point.';
$string['invalidtimecode'] = 'Use seconds, MM:SS or HH:MM:SS.';
$string['managepoints'] = 'Gerenciar pontos de previsão';
$string['markcorrectness'] = 'Correção';
$string['maximumgrade'] = 'Maximum grade';
$string['modulename'] = 'Previsão em Vídeo';
$string['modulename_help'] = 'Pauses a video at prediction points so learners must predict what happens next before continuing.';
$string['modulenameplural'] = 'Previsões em Vídeo';
$string['noactivities'] = 'There are no Video Prediction activities in this course.';
$string['nopoints'] = 'No prediction points have been created yet.';
$string['notgraded'] = 'Não avaliado';
$string['pauseonreveal'] = 'Pausar novamente no momento da revelação';
$string['pending'] = 'Pendente';
$string['pluginadministration'] = 'Administração de Previsão em Vídeo';
$string['pluginname'] = 'Previsão em Vídeo';
$string['pointdeleted'] = 'Prediction point deleted.';
$string['pointnotreached'] = 'This prediction point has not been reached yet.';
$string['points'] = 'Points';
$string['pointsaved'] = 'Prediction point saved.';
$string['pointtitle'] = 'Título do ponto';
$string['poster'] = 'Poster image';
$string['posterurl'] = 'Poster URL';
$string['predictionlocked'] = 'This prediction has already been submitted and cannot be changed.';
$string['predictionlockednotice'] = 'Your original prediction is locked after submission and cannot be changed.';
$string['predictionpoint'] = 'Ponto de previsão';
$string['predictionpoints'] = 'Pontos de previsão';
$string['predictionquestion'] = 'Pergunta de previsão';
$string['predictionrequired'] = 'A previsão é obrigatória antes de continuar';
$string['predictions'] = 'Previsões';
$string['predictiontimeposition'] = 'Momento da previsão';
$string['preventseek'] = 'Prevent advancing into unviewed content';
$string['preventseek_help'] = 'Also blocks advancement past any unanswered mandatory prediction point.';
$string['privacy:metadata:progress'] = 'Stores consolidated video viewing progress.';
$string['privacy:metadata:progress:completed'] = 'Completion state.';
$string['privacy:metadata:progress:duration'] = 'Known video duration.';
$string['privacy:metadata:progress:lastposition'] = 'Last playback position.';
$string['privacy:metadata:progress:maxwatched'] = 'Highest playback position reached.';
$string['privacy:metadata:progress:percent'] = 'Watched percentage.';
$string['privacy:metadata:progress:segments'] = 'Consolidated watched segments.';
$string['privacy:metadata:progress:timemodified'] = 'Last progress update time.';
$string['privacy:metadata:progress:totalwatchtime'] = 'Total playback time.';
$string['privacy:metadata:progress:uniquewatched'] = 'Unique seconds watched.';
$string['privacy:metadata:progress:userid'] = 'User identifier.';
$string['privacy:metadata:responses'] = 'Stores predictions, grading and post-result reflections.';
$string['privacy:metadata:responses:awarded'] = 'Points awarded.';
$string['privacy:metadata:responses:iscorrect'] = 'Whether the prediction was marked correct.';
$string['privacy:metadata:responses:predictiontime'] = 'Prediction submission time.';
$string['privacy:metadata:responses:reflection'] = 'Post-result reflection.';
$string['privacy:metadata:responses:reflectiontime'] = 'Reflection submission time.';
$string['privacy:metadata:responses:response'] = 'Original immutable prediction response.';
$string['privacy:metadata:responses:understandingchanged'] = 'Whether the learner reported a change in understanding.';
$string['privacy:metadata:responses:userid'] = 'User identifier.';
$string['privacy:progresspath'] = 'Video progress';
$string['privacy:responsespath'] = 'Predictions and reflections';
$string['reflection'] = 'Reflexão';
$string['reflectionlocked'] = 'This reflection has already been submitted and cannot be changed.';
$string['reflectionquestion'] = 'Pergunta de reflexão após o resultado';
$string['reporttitle'] = 'Video Prediction report';
$string['responseisrequired'] = 'A response is required.';
$string['responsemultiplechoice'] = 'Múltipla escolha';
$string['responseopen'] = 'Resposta aberta';
$string['responseoptions'] = 'Options';
$string['responseoptions_help'] = 'One option per line. Use key|Label to set an explicit stored value, for example A|The object falls.';
$string['responseoutcomes'] = 'Possíveis resultados';
$string['responsetruefalse'] = 'Verdadeiro / falso';
$string['responsetype'] = 'Tipo de resposta';
$string['resultheader'] = 'Result and reflection';
$string['resultnotreached'] = 'The result reveal point has not been reached yet.';
$string['resulttext'] = 'Resultado real / explicação';
$string['resumeplayback'] = 'Resume from last position';
$string['revealposition'] = 'Result reveal time';
$string['revealposition_help'] = 'Time when the actual outcome becomes available. Leave blank to use prediction time + 10 seconds.';
$string['savereflection'] = 'Salvar reflexão';
$string['seekblocked'] = 'You cannot advance beyond the currently unlocked point.';
$string['sortorder'] = 'Sort order';
$string['sourceupload'] = 'Uploaded video';
$string['sourceurl'] = 'Direct URL / HLS';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['student'] = 'Aluno';
$string['studentdetails'] = 'Detalhes do aluno';
$string['submitprediction'] = 'Registrar previsão';
$string['timeline'] = 'Prediction timeline';
$string['type:multichoice'] = 'Múltipla escolha';
$string['type:open'] = 'Resposta aberta';
$string['type:outcomes'] = 'Possíveis resultados';
$string['type:truefalse'] = 'Verdadeiro / falso';
$string['understandingchanged'] = 'Mudou o entendimento';
$string['understandingchangedno'] = 'No, my understanding did not change';
$string['understandingchangedyes'] = 'Yes, my understanding changed';
$string['understandingchanges'] = 'Mudanças de entendimento';
$string['videofile'] = 'Video file';
$string['videoheader'] = 'Video';
$string['videopredict:addinstance'] = 'Add a new Video Prediction activity';
$string['videopredict:exportreport'] = 'Export Video Prediction reports';
$string['videopredict:grade'] = 'Grade Video Prediction responses';
$string['videopredict:managepoints'] = 'Gerenciar pontos de previsão';
$string['videopredict:view'] = 'View Video Prediction activity';
$string['videopredict:viewreport'] = 'View Video Prediction reports';
$string['videopredictname'] = 'Nome da atividade';
$string['videosource'] = 'Video source';
$string['videourl'] = 'Video URL or ID';
$string['videourl_help'] = 'For YouTube/Vimeo you may paste the full URL or video ID. Direct URLs may point to MP4/WebM or an HLS stream supported by the browser.';
$string['viewdetails'] = 'Ver detalhes';
$string['watchedpercent'] = 'Assistido';
$string['watchedpercentvalue'] = 'Watched: {$a}%';
$string['yourprogress'] = 'Seu progresso';
$string['false'] = 'Falso';
$string['true'] = 'Verdadeiro';\n$string['completionrules'] = 'Regras de conclusão';\n\n$string['errormaxfiles'] = 'Somente um arquivo pode ser enviado.';\n\n$string['completiondetail:percent'] = 'Assistir a pelo menos {$a}% do vídeo';\n\n$string['completiondetail:predictions'] = 'Fazer todas as previsões obrigatórias';\n\n$string['completionpercent'] = 'Exigir percentual assistido';\n\n$string['completionpredictions'] = 'Exigir todas as previsões obrigatórias';\n\n$string['correctanswer'] = 'Valor da opção correta';\n\n$string['correctanswer_help'] = 'Informe a chave da opção, por exemplo A, 1, verdadeiro ou falso. Deixe em branco quando não houver uma previsão objetivamente correta.';\n\n$string['gradeheader'] = 'Nota';\n\n$string['grademode'] = 'Cálculo da nota';\n\n$string['grademodeblended'] = '50% pontuação das previsões + 50% percentual assistido';\n\n$string['grademodepredictions'] = 'Pontuação das previsões';\n\n$string['grademodeprogress'] = 'Percentual assistido';\n\n$string['invalidgrade'] = 'A nota ou os pontos não podem ser negativos.';\n\n$string['invalidpercent'] = 'Informe um percentual de 0 a 100.';\n\n$string['invalidresponse'] = 'A resposta selecionada não é válida para este ponto de previsão.';\n\n$string['invalidtimecode'] = 'Use segundos, MM:SS ou HH:MM:SS.';\n\n$string['maximumgrade'] = 'Nota máxima';\n\n$string['modulename_help'] = 'Pausa um vídeo em pontos definidos para que os estudantes prevejam o que acontecerá antes de continuar.';\n\n$string['noactivities'] = 'Não há atividades de Previsão em Vídeo neste curso.';\n\n$string['nopoints'] = 'Nenhum ponto de previsão foi criado ainda.';\n\n$string['pointdeleted'] = 'Ponto de previsão excluído.';\n\n$string['pointnotreached'] = 'Este ponto de previsão ainda não foi alcançado.';\n\n$string['points'] = 'Pontos';\n\n$string['pointsaved'] = 'Ponto de previsão salvo.';\n\n$string['poster'] = 'Imagem de capa';\n\n$string['posterurl'] = 'URL da imagem de capa';\n\n$string['predictionlocked'] = 'Esta previsão já foi enviada e não pode ser alterada.';\n\n$string['predictionlockednotice'] = 'Sua previsão original fica bloqueada após o envio e não pode ser alterada.';\n\n$string['preventseek'] = 'Impedir avanço para conteúdo ainda não assistido';\n\n$string['preventseek_help'] = 'Também impede avançar além de qualquer ponto de previsão obrigatório ainda não respondido.';\n\n$string['privacy:metadata:progress'] = 'Armazena o progresso consolidado de visualização do vídeo.';\n\n$string['privacy:metadata:progress:completed'] = 'Estado de conclusão.';\n\n$string['privacy:metadata:progress:duration'] = 'Duração conhecida do vídeo.';\n\n$string['privacy:metadata:progress:lastposition'] = 'Última posição de reprodução.';\n\n$string['privacy:metadata:progress:maxwatched'] = 'Maior posição de reprodução alcançada.';\n\n$string['privacy:metadata:progress:percent'] = 'Percentual assistido.';\n\n$string['privacy:metadata:progress:segments'] = 'Segmentos assistidos consolidados.';\n\n$string['privacy:metadata:progress:timemodified'] = 'Data da última atualização do progresso.';\n\n$string['privacy:metadata:progress:totalwatchtime'] = 'Tempo total de reprodução.';\n\n$string['privacy:metadata:progress:uniquewatched'] = 'Segundos únicos assistidos.';\n\n$string['privacy:metadata:progress:userid'] = 'Identificador do usuário.';\n\n$string['privacy:metadata:responses'] = 'Armazena previsões, avaliações e reflexões após o resultado.';\n\n$string['privacy:metadata:responses:awarded'] = 'Pontos atribuídos.';\n\n$string['privacy:metadata:responses:iscorrect'] = 'Indica se a previsão foi marcada como correta.';\n\n$string['privacy:metadata:responses:predictiontime'] = 'Data de envio da previsão.';\n\n$string['privacy:metadata:responses:reflection'] = 'Reflexão após o resultado.';\n\n$string['privacy:metadata:responses:reflectiontime'] = 'Data de envio da reflexão.';\n\n$string['privacy:metadata:responses:response'] = 'Resposta original e imutável da previsão.';\n\n$string['privacy:metadata:responses:understandingchanged'] = 'Indica se o estudante informou mudança de entendimento.';\n\n$string['privacy:metadata:responses:userid'] = 'Identificador do usuário.';\n\n$string['privacy:progresspath'] = 'Progresso do vídeo';\n\n$string['privacy:responsespath'] = 'Previsões e reflexões';\n\n$string['reflectionlocked'] = 'Esta reflexão já foi enviada e não pode ser alterada.';\n\n$string['reporttitle'] = 'Relatório de Previsão em Vídeo';\n\n$string['responseisrequired'] = 'Uma resposta é obrigatória.';\n\n$string['responseoptions'] = 'Opções';\n\n$string['responseoptions_help'] = 'Uma opção por linha. Use chave|Rótulo para definir um valor armazenado explícito, por exemplo A|O objeto cai.';\n\n$string['resultheader'] = 'Resultado e reflexão';\n\n$string['resultnotreached'] = 'O momento de revelação do resultado ainda não foi alcançado.';\n\n$string['resumeplayback'] = 'Retomar da última posição';\n\n$string['revealposition'] = 'Momento de revelação do resultado';\n\n$string['revealposition_help'] = 'Momento em que o resultado real fica disponível. Deixe em branco para usar o momento da previsão + 10 segundos.';\n\n$string['seekblocked'] = 'Você não pode avançar além do ponto atualmente liberado.';\n\n$string['sortorder'] = 'Ordem';\n\n$string['sourceupload'] = 'Vídeo enviado';\n\n$string['sourceurl'] = 'URL direta / HLS';\n\n$string['sourcevimeo'] = 'Vimeo';\n\n$string['sourceyoutube'] = 'YouTube';\n\n$string['timeline'] = 'Linha do tempo das previsões';\n\n$string['understandingchangedno'] = 'Não, meu entendimento não mudou';\n\n$string['understandingchangedyes'] = 'Sim, meu entendimento mudou';\n\n$string['videofile'] = 'Arquivo de vídeo';\n\n$string['videoheader'] = 'Vídeo';\n\n$string['videopredict:addinstance'] = 'Adicionar uma nova atividade de Previsão em Vídeo';\n\n$string['videopredict:exportreport'] = 'Exportar relatórios de Previsão em Vídeo';\n\n$string['videopredict:grade'] = 'Avaliar respostas da Previsão em Vídeo';\n\n$string['videopredict:view'] = 'Visualizar atividade de Previsão em Vídeo';\n\n$string['videopredict:viewreport'] = 'Visualizar relatórios de Previsão em Vídeo';\n\n$string['videosource'] = 'Fonte do vídeo';\n\n$string['videourl'] = 'URL ou ID do vídeo';\n\n$string['videourl_help'] = 'Para YouTube/Vimeo, cole a URL completa ou o ID do vídeo. URLs diretas podem apontar para MP4/WebM ou para um stream HLS.';\n\n$string['watchedpercentvalue'] = 'Assistido: {$a}%';\n\n$string['eventpredictionsubmitted'] = 'Previsão enviada';\n\n$string['eventreflectionsubmitted'] = 'Reflexão enviada';\n\n$string['eventpredictiongraded'] = 'Previsão avaliada';\n\n$string['eventpointcreated'] = 'Ponto de previsão criado';\n\n$string['eventpointupdated'] = 'Ponto de previsão atualizado';\n\n$string['eventpointdeleted'] = 'Ponto de previsão excluído';\n\n$string['optionsrequired'] = 'Adicione pelo menos uma opção de resposta.';\n\n$string['invalidcorrectanswer'] = 'A resposta correta deve corresponder a uma das chaves das opções.';\n\n$string['revealbeforeprediction'] = 'O momento de revelação não pode ser anterior ao momento da previsão.';\n\n$string['resetuserdata'] = 'Excluir respostas, reflexões, progresso e notas da Previsão em Vídeo';\n\n$string['hlsunsupported'] = 'Este navegador não consegue reproduzir este stream HLS.';\n\n$string['privacy:metadata:responses:responsejson'] = 'Dados estruturados da resposta.';\n\n$string['privacy:metadata:responses:gradedby'] = 'Identificador do usuário que avaliou manualmente a previsão.';\n\n$string['privacy:metadata:responses:timemodified'] = 'Data da última alteração da resposta ou da avaliação.';\n\n$string['privacy:gradingpath'] = 'Atividade de avaliação manual';\n