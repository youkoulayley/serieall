<script src="//code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.js"></script>
<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.16/js/dataTables.semanticui.min.js"></script>
<script src="//cdn.ckeditor.com/4.7.3/full/ckeditor.js"></script>
<script src="/spoiler/spoiler.js"></script>

<script>
    $(document).ready(function() {
        $('#footer .icon').hover(function () {
            $(this).transition('tada');
        }, function () {
        });

        $('.coupled.modal').modal({
            allowMultiple: true
        })
        ;

        $('#clickLogin').on('click', function(){
            $('#loginModal').modal({
                transition: 'drop down'
            })
                .modal('show')
            ;
        });

        $('#clickRegister').on('click', function(){
            $('#registerModal').modal({
                transition: 'drop down'
            })
                .modal('show')
            ;
        });

        $('.second.modal')
            .modal({
                transition: 'fly left'
            })
            .modal('attach events', '#goToSecondModal')

        ;

        $('.dropdown')
            .dropdown()
        ;

        $('.message .close')
            .on('click', function () {
                $(this)
                    .closest('.message')
                    .transition('fade')
                ;
            })
        ;

        $('#showDropdown')
            .search({
                apiSettings: {
                    url: '/api/shows/search?_q={query}'
                },
                fields: { results: "data", title: "name", url: "url" },
                selectFirstResult: true,
                minCharacters: 1,
                maxResults: 40
            });

        $('.ui.inline.cookie.nag')
            .nag({
                key      : 'accepts-cookies',
                value    : true
            })
        ;

        $('.clickToConnect').click(function() {
            $('#login')
                .modal('show')
            ;
        });

        $(document).on('submit', '#formLogin', function(e) {
            e.preventDefault();
            var buttonSubmit = '#formLogin .submit';

            $(buttonSubmit).addClass("loading");

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json"
            })
                .done(function(data) {
                    $('#login').modal('hide');
                    window.location.reload(false);
                })
                .fail(function(data) {
                    $(buttonSubmit).removeClass("loading");

                    $.each(data.responseJSON.errors, function (key, value) {
                        var input = '#formLogin input[name=' + key + ']';

                        $(input + '+div').removeClass('hidden');
                        $(input + '+div').text(value);

                        $(input).parent().addClass('error');
                    });
                });
        });

        $(document).on('submit', '#formRegister', function(e) {
            e.preventDefault();
            var buttonSubmit = '#formRegister .submit';
            var positiveMessage = '#formRegister .positive.message';
            var captchaError = '#formRegister .captchaError';

            // Reinitialiser tous les messages d'erreur
            $('#formRegister input+div').addClass('hidden');
            $('#formRegister input').parent().removeClass('error');

            $(buttonSubmit).addClass("loading");

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: "json"
            })
                .done(function(data) {
                    $(buttonSubmit).removeClass("loading");
                    $(positiveMessage).removeClass('hidden');
                })
                .fail(function(data) {
                    $(buttonSubmit).removeClass("loading");

                    $.each(data.responseJSON.errors, function (key, value) {
                        var input = '#formRegister input[name=' + key + ']';

                        $(input + '+div').removeClass('hidden');
                        $(input + '+div').text(value);

                        $(input).parent().addClass('error');

                        if(key = 'g-recaptcha-response'){
                            $(captchaError).text(value);
                            $(captchaError).removeClass('hidden');
                        }
                    });
                });
        });

    })
</script>

@yield('scripts')