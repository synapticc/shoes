// assets/js/store/change-password.js


/*
  Page:  Change password page
  Route: /forgot-password/new
*/
if (document.querySelector('form[name="change_password_form"]') !== null)
{
    const changePasswordForm = document.querySelector('form[name="change_password_form"]'),
    password = changePasswordForm.querySelector('input[name="change_password_form[plainPassword][first]"]'),
    confirmPassword = changePasswordForm.querySelector('input[name="change_password_form[plainPassword][second]"]'),
    passwordShow = password.parentElement.querySelector('span[id="password-eye-icon"]'),
    confirmPasswordShow = confirmPassword.parentElement.querySelector('span[id="password-eye-icon"]'),
    resetBtn = changePasswordForm.querySelector('button');

    /**
     * Password validation RegEx for JavaScript
     *
     * Passwords must be
     * - At least 6 characters long, max length anything
     * - Include at least 1 lowercase letter
     * - 1 capital letter
     * - 1 number
     * - 1 special character => !@#$%^&*
     *
     */
    function validatePassword(password){

         var regexPassword = /^(?=.*[\d])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*])[\w!@#$%^&*]{6,}$/;
         return regexPassword.test(password);
    }

    let validateConfirmPassword = (password, confirmPassword) => {
      return (password === confirmPassword) ? 'matched' : 'failed';
    };

    function toggleInputType(input){
      if (input.getAttribute('type') == 'password')
        input.setAttribute('type','text');
      else if (input.getAttribute('type') == 'text')
        input.setAttribute('type','password');
    }

    function toggleVisibility()
    {
        let input = this.parentElement.previousElementSibling;
          // <input id="change_password_form_plainPassword_first" name="change_password_form[plainPassword][first]" >

        if (this.getAttribute('class') == 'far fa-eye-slash')
        {
            this.setAttribute('class','far fa-eye');
            toggleInputType(input);
        }
        else if (this.getAttribute('class') == 'far fa-eye')
        {
            this.setAttribute('class','far fa-eye-slash');
            toggleInputType(input);
        }
    }

    function enable(input) {
      if (input.hasAttribute('disabled'))
      {
          input.removeAttribute('disabled');
      }
    }

    function disable(input) {
      if (input.hasAttribute('disabled') == false)
      {
          input.setAttribute('disabled','disabled');
      }
    }

    password.addEventListener('input',
      () => {
      if (password.value == '')
        confirmPassword.value = '';

      if (validatePassword(password.value) == true)
      {
          enable(confirmPassword);
          // passwordCheckIcon.style.display = 'inline';
      }
      else if (validatePassword(password.value) == false)
      {
        disable(confirmPassword);
        disable(resetBtn);

      }

    });

    confirmPassword.addEventListener('input',
      () => {
      if (password.value == '')
        confirmPassword.value = '';

      if (validatePassword(confirmPassword.value) == true)
      {
          enable(resetBtn);
      }
      else if (validatePassword(confirmPassword.value) == false)
      {
        disable(resetBtn);

      }

    });

    window.addEventListener('load',
      () => {
      if (password.value == '')
      {
        confirmPassword.value = '';
        disable(confirmPassword);
      }

    });

    /*
      Show password when eye icon is clicked
    */
    passwordShow.addEventListener('click', toggleVisibility);
    confirmPasswordShow.addEventListener('click', toggleVisibility);
}
