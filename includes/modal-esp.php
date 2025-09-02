<?php include_once('php/send-consulta-modal.php');  ?>

<!-- Modal Esp -->
<div class="modal fade modales" id="consultaEsp" tabindex="-1" aria-labelledby="consultaEspLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="consultaEspLabel">Realizar Consulta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
         <form id="send" method="post" class="needs-validation" enctype="multipart/form-data" novalidate>

            <!-- Errores Formulario -->
            <?php if ($errors): ?>
               <div id="error" class="alert alert-danger alert-dismissible fade show fadeInLeft" role="alert">
                 <strong>¡Por favor verificá los datos!</strong>
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
                 <ul style="padding: 0;">
                   <?php foreach ($errors as $error) { ?>
                     <li>- <?php echo $error; ?></li>
                   <?php } ?>
                 </ul>
               </div>
            <?php endif ?>
            <!-- Errores Formulario end -->

            <input type="hidden" name="origin" value="<?= $origin ?>">

            <div class="form-group">
               <input required type="text" class="form-control transition" name="name" value="<?= $name ?>" placeholder="Nombre Completo">
               <div class="invalid-feedback">
                 Ingrese su nombre.
               </div>
            </div>

            <div class="form-group">
               <input required type="email" class="form-control transition" name="email" value="<?= $email ?>" placeholder="E-mail">
               <div class="invalid-feedback">
                 Ingrese un email válido.
               </div>
            </div>

            <div class="form-group">
               <input required type="text" class="form-control transition" name="phone" value="<?= $phone ?>" placeholder="Teléfono">
               <div class="invalid-feedback">
                 Ingrese un teléfono.
               </div>
            </div>

            <div class="form-group">
               <textarea required class="form-control transition" name="comments" rows="3" placeholder="Consulta"><?= $comments ?></textarea>
               <div class="invalid-feedback">
                 Ingrese su consulta.
               </div>
            </div>

            <div class="form-group">
              <div class="custom-file">
                <input type="file" lang="es" class="custom-file-input" name="cv" id="customFile">
                <label class="custom-file-label" for="customFile">Adjuntar CV</label>
                <small id="customFilelHelp" class="form-text text-muted">(máx: 2mb) (PDF, DOC, DOCX, JPG, PNG & GIF).</small>
                <div class="invalid-feedback">
                  (máx: 2mb) (PDF, DOC, DOCX, JPG, PNG & GIF).
                </div>
              </div>
            </div>

            <div class="referencia">
              <div class="form-group">
                 <input tabindex="-1" v-model="formConsultPosition" name="position" type="text" class="form-control transition" id="position" name="position">
              </div>

              <div class="form-group">
                 <input tabindex="-1" v-model="formConsultLocation" name="location" type="text" class="form-control transition" id="location" name="location">
              </div>

              <div class="form-group">
                 <input tabindex="-1" v-model="formConsultJobFunction" name="jobFunction" type="text" class="form-control transition" id="jobFunction" name="jobFunction">
              </div>

              <div class="form-group">
                 <input tabindex="-1" v-model="formConsultEmploymentType" name="employmentType" type="text" class="form-control transition" id="type" name="type">
              </div>
            </div>


            <div class="form-group">
               <div id="recaptcha" class="g-recaptcha" data-sitekey="<?= RECAPTCHA_PUBLIC_KEY ?>"></div>
            </div>

            <div class="text-right">
               <button type="submit" name="send" class="btn btn-primary transition">ENVIAR</button>
            </div>

         </form>
      </div>

    </div>
  </div>
</div>
<!-- Modal Esp end -->