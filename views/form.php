<form action="" method="post" class="fpbx-submit" id="hwform" name="hwform" data-fpbx-delete="config.php?display=returnontransfer">
  <input type="hidden" name='action' value="save">

  <!--token-->
  <div class="element-container">
    <div class="row">
      <div class="form-group">
        <div class="col-md-3">
          <label class="control-label" for="token"><?php echo _("Token") ?></label>
          <i class="fa fa-question-circle fpbx-help-icon" data-for="token"></i>
        </div>
        <div class="col-md-9">
          <input type="text" class="form-control" id="alertinfo" name="alertinfo" value="<?php echo $settings['token'];?>">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <span id="alertinfo-help" class="help-block fpbx-help-block"><?php echo _("Autentication token to be used in API POST header for authentication")?></span>
      </div>
    </div>
  </div>
  <!--token end-->

</form>

