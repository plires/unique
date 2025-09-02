<!-- Modal Add / Edit -->
<div class="modal fade" id="modalAddJob" tabindex="-1" role="dialog" aria-labelledby="modalAddJobLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">

			<form id="formData" method="post" @submit="submitFormJob">
				<div class="modal-header" :class="{'bg-warning text-dark' : jobEdit, 'bg-primary text-white' : !jobEdit}">
					<h5 class="modal-title" id="modalAddJobLabel">{{ titleForm }}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">

					<div class="form-group">
						<input type="text" v-model="position" class="form-control" id="position" name="position" placeholder="Posición">
					</div>

					<div class="form-group">
						<input type="text" v-model="location" class="form-control" id="location" name="location" placeholder="Ubicación">
					</div>

					<div class="form-group">
						<input type="text" v-model="job_function" class="form-control" id="job_function" name="job_function" placeholder="Función laboral">
					</div>

					<div class="form-group">
						<input type="text" v-model="employment_type" class="form-control" id="employment_type" name="employment_type" placeholder="Tipo de empleo">
					</div>

					<div class="form-group">
						<textarea v-model="description" class="form-control" id="description" name="description" rows="6" placeholder="Descripción del Puesto"></textarea>
					</div>

					<div class="form-group">
						<input v-model="link" type="text" class="form-control" id="link" name="link" placeholder="example: https://linkedin.com/...">
					</div>

					<div v-if="errors.length" id="error" class="alert alert-danger alert-dismissible fade show fadeInLeft mt-3" role="alert">
						<strong>Por favor, corrija el/los siguiente/s error/es:</strong>
						<button @click="errors = []" type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<ul>
							<li v-for="(error, index) in errors" :key="index">{{ error }}</li>
						</ul>
					</div>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
					<button type="submit" :class="['btn', jobEdit ? 'bg-warning' : 'bg-primary text-white']">Enviar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- Modal Add / Edit end -->