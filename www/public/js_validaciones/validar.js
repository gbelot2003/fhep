$(document).ready(function() {
    $('#ncontra').keyup(function() {
        $('#strengthMessage').html(checkStrength($('#ncontra').val()))
    })

    function checkStrength(password) {
        var strength = 0
        if (password.length < 7) {
            $('#strengthMessage').removeClass()
            $('#strengthMessage').addClass('Short')
            return 'la contraseña es demasiada corta'
        }
        // If password contains both lower and uppercase characters, increase strength value.
        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
            // If it has numbers and characters, increase strength value.
        if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
            // If it has one special character, increase strength value.
        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~,-])/)) strength += 1
            // If it has two special characters, increase strength value.

        // Calculated strength value, we can return messages
        // If value is less than 2
        if (strength < 2) {
            $('#strengthMessage').removeClass()
            $('#strengthMessage').addClass('Weak')
            return 'Débil'
        } else if (strength == 2) {
            $('#strengthMessage').removeClass()
            $('#strengthMessage').addClass('Weak')
            return 'Bueno'
        } else {
            $('#strengthMessage').removeClass()
            $('#strengthMessage').addClass('Weak')
            $('#fuerte').val('1');
            return 'Fuerte'

        }
    }
});