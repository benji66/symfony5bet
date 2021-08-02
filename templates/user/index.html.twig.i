{% extends '@FgimenezAdminlte/layout.admin.html.twig' %}

{% block title %}User index{% endblock %}

{% block stylesheets %}
 <link rel="stylesheet" href="/bundles/fgimenezadminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
{% endblock %}


{% block body %}
    <h1>User index FRAN</h1>

    {# read and display all flash messages #}
{% for label, messages in app.flashes %}
    {% for message in messages %}
    
        <div class="alert alert-{{ label }} alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <!-- <h5><i class="icon fas fa-check"></i> Alert!</h5> -->
                  {{ message }}
        </div>
    {% endfor %}
{% endfor %}
           
 <!-- SEARCH FORM -->
   {{ form_start(form, { attr: {novalidate: 'novalidate'}, 'method': 'GET'}) }}

        <div class="form-group">
                  {{ form_label(form.email,'Correo:') }}

                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-at"></i></span>
                    </div>
                    {{ form_widget(form.email, {'attr': {'value': user.email}}) }}


                    <small>{{ form_help(form.email) }}</small>
                  </div>
                  <!-- /.input group -->
                  <div class="form-error">
                {{ form_errors(form.email) }}
            </div>
        </div>

 
        <button  type="button" class="btn btn-primary" 
         onclick="buscar(this.form)">
             <i class="fas fa-search"></i>{{ button_label|default('Search') }}
        </button>

         <button type="button" class="btn btn-primary" 
         onclick="descargaPDF(this.form)">
             <i class="far fa-file-pdf"></i>{{ button_label|default('PDF') }}
        </button>

        <a href="{{ path('user_index') }}">Limpiar</a>

        {{ form_widget(form._token) }} 
    {{ form_end(form, {'render_rest': false}) }} 
 <!-- //SEARCH FORM -->

           <div class="card-header">
              <h3 class="card-title">DataTable with minimal features  hover style</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
     <table id="example2" class="table table-bordered table-hover">
        <thead>
            <tr>              
                <th>Email</th>
                <th>Roles</th>             
                <th>actions</th>
            </tr>
        </thead>
        <tbody>
        {% for user in users %}
            <tr>               
                <td>{{ user.email }}</td>
                <td>{{ user.roles ? user.roles|json_encode : '' }}</td>             
                <td>
                    <a href="{{ path('user_show', {'id': user.id}) }}"><i class="far fa-eye">mostrar</i></a>
                    <a href="{{ path('user_edit', {'id': user.id}) }}"><i class="fas fa-edit">edit</i></a>
                </td>
            </tr>
        {% else %}
            <tr>
                <td colspan="5">no records found</td>
            </tr>
        {% endfor %}
        </tbody>

        <tfoot>
            <tr>
                <th>Email</th>
                <th>Roles</th>
                <th>actions</th>
            </tr>
        </tfoot>
    </table>

   <div class="navigation float-right">
            {{ knp_pagination_render(users) }}
        </div>
    <a href="{{ path('user_new') }}">
        <i class="fas fa-plus"></i>Nuevo
    </a> 
    
    
    </div>
 </div>
{% endblock %}

{% block javascripts %}

<!-- DataTables -->
<script src="/bundles/fgimenezadminlte/plugins/datatables/jquery.dataTables.js"></script>

<script>
function descargaPDF(form){
    $(form).attr('target', '_blank');
    $(form).attr('action', "{{ path('user_pdf') }}");   
    $(form).submit();
}

function buscar(form){
    $(form).attr('target', ' ');
    $(form).attr('action', "{{ path('user_index') }}");   
    $(form).submit();
} 



  $(function () {

    $('#example2').DataTable({
      "paging": false,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": false,
      "autoWidth": false,
    });
  });
</script>

{% endblock %}




