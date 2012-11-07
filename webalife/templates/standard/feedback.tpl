<div class="modal hide fade" id="sendMessage" tabindex="-1" role="dialog" aria-labelledby="sendMessageLabel2"
     aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 id="sendMessageLabel2">Отправка сообщения</h3>
    </div>
    <div class="modal-body">
        <div id="feedback-error" class="alert alert-error hide">

        </div>
        <form action="?signId={$smarty.get.signId}&cmd=feedback" id="feedback" method="post" class="form-horizontal">
            <input type="hidden" name="feedbackUserId" value="{$data.USER_ID}"/>
            <div class="control-group">
                <label class="control-label" for="inputSubject">Тема</label>

                <div class="controls">
                    <input type="text" id="inputSubject" name="inputSubject" placeholder="Тема письма">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="inputMessage">Сообщение</label>

                <div class="controls">
                    <textarea id="inputMessage" name="inputMessage" placeholder="Текст сообщения" rows="5"
                              cols="30"></textarea>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button id="btnSendFeedback" class="btn btn-primary">Отправить &raquo;</button>
    </div>
</div>