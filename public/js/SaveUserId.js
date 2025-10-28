const dataDestinatarioId = document.querySelectorAll('.btn-desafiar');
const formulario = document.querySelector('.form-desafiar');

dataDestinatarioId.forEach(button => {
    button.addEventListener('click', function () {
        const destinatarioId = this.getAttribute('data-destinatario-id');
        document.getElementById('destinatario_id').value = destinatarioId;

        formulario.submit();
    });

});