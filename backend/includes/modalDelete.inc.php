<!-- Modal Delete confirmacion -->
<div class="modal fade" id="formDelete" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="formDeleteLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
	  <div class="modal-content">
	    <div class="modal-header alert-danger">
	      <h5 class="modal-title" id="formDeleteLabel">Confirmación</h5>
	      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	        <span aria-hidden="true">&times;</span>
	      </button>
	    </div>
	    <div class="modal-body text-center">
	      <p>Esta operación no se puede deshacer.</p>
	      <p><strong>¿Desea continuar?</strong></p>
	    </div>
	    <div class="modal-footer">
	      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
	      <button @click="deleteJob(idJobToDelete)" type="button" class="btn btn-danger">Eliminar</button>
	    </div>
	  </div>
	</div>
</div>
<!-- Modal Delete confirmacion end -->