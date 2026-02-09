// assets/js/admin/confirm-password.js

import { validateEmail, validatePassword, isBothPasswordMatch,
         enable, disable, toggleVisibility } from '../module.js';

/* Page: Sign up
   Route: /sign-up
*/
if (document.querySelector('input[name="registration_form[email]"]') !== null)
{
    const email = document.querySelector('input[name="registration_form[email]"]');
    const password = document.querySelector('input[name="registration_form[plainPassword]"]');
    const confirmPassword = document.querySelector('input[name="registration_form[confirmPassword]"]');
    const agreeTerms = document.querySelector('input[name="registration_form[agreeTerms]"]');
    const signUpBtn = document.getElementById('registration_form_sign_up');
    const passwordShow = document.getElementById('password-eye-icon');
    const confirmPasswordShow = document.getElementById('confirm-password-eye-icon');
    const passwordCheckIcon = document.getElementById('password-check-icon');
    const confirmPasswordCheckIcon = document.getElementById('confirm-password-check-icon');
    const wrongPasswordCrossIcon = document.getElementById('wrong-password-cross-icon');

    /* Multi-level validation
     If email entered is valid, then enable the:
      1) 'Agree to term' checkbox

    If email entered is invalid, then disable the:
     1) Password field
     2) Confirm password field
     3) 'Agree to term' checkbox
     4) Sign up button
    */
    function afterValidEmailSignUp()
    {
      if (validateEmail(email.value) == true)
      {
        enable(password);

        if (password.value != '')
          if (isBothPasswordMatch(password.value,confirmPassword.value) == 'matched')
            enable(agreeTerms);

        if (isBothPasswordMatch(password.value,confirmPassword.value) == 'matched')
          enable(agreeTerms);
      }
      else if (validateEmail(email.value) == false)
      {
        disable(password);
        disable(confirmPassword);
        disable(agreeTerms);
        disable(signUpBtn);
      }
    }

    email.addEventListener('input', afterValidEmailSignUp );

    window.addEventListener('load', afterValidEmailSignUp );

    /*
     If password entered is valid, then enable the:
      1) Confirm password field
      2) Display success check icon

     If password entered is invalid, then disable the:
      1) Confirm password field
      2) 'Agree to term' checkbox
      3) Sign up button */
    password.addEventListener('input',
      () =>
      {
        /*
         If password field is empty,
         then empty confirm password field. */
        if (password.value == '')
          confirmPassword.value = '';

        if (validatePassword(password.value) == true)
        {
          enable(confirmPassword);
          passwordCheckIcon.style.display = 'inline';
        }
        else if (validatePassword(password.value) == false)
        {
          disable(confirmPassword);
          disable(agreeTerms);
          disable(signUpBtn);
        }
      });

    /*
     If both password and confirm password match, then
     1) Enable the 'Agree to term' checkbox
     2) Hide failure cross icon
     3) Display success check icon

     If both password and confirm password don't match, then
     1) Disable the 'Agree to term' checkbox
     2) Disable Sign up button
     3) Hide success check icon
     4) Display failure cross icon  */
    confirmPassword.addEventListener('input',
      () =>
      {
        if (isBothPasswordMatch(password.value,confirmPassword.value) == 'matched')
        {
          enable(agreeTerms);
          wrongPasswordCrossIcon.style.display = 'none';
          confirmPasswordCheckIcon.style.display = 'inline';
        }

        else if (isBothPasswordMatch(password.value,confirmPassword.value) == 'failed')
        {
          disable(agreeTerms);
          disable(signUpBtn);
          confirmPasswordCheckIcon.style.display = 'none';
          wrongPasswordCrossIcon.style.display = 'inline';
        }
      });

    /*
     If 'Agree to terms' is checked, then enable the Sign up button
     Otherwise, disable the Sign up button  */
    agreeTerms.addEventListener('input',
      () =>
      {
        if (agreeTerms.checked)
          enable(signUpBtn);
        else if (agreeTerms.checked == false)
          disable(signUpBtn);
      });

    /* Show password when eye icon is clicked */
    passwordShow.addEventListener('click', toggleVisibility);

    confirmPasswordShow.addEventListener('click', toggleVisibility);

    /* Before submitting the form, reset password input type
       back to 'password' instead of 'text'.*/
    signUpBtn.addEventListener('click',
      (e) =>
      {
        if (password.getAttribute('type') == 'text')
          password.setAttribute('type','password');

        if (confirmPassword.getAttribute('type') == 'text')
          confirmPassword.setAttribute('type','password');
      });
}

/* Page: Login
   Route: /login
*/
if (document.querySelector('#login_form') !== null)
{
    const loginForm = document.getElementById('login_form');
    const email = loginForm.querySelector('input[name="email"]');
    const password = loginForm.querySelector('input[name="password"]');
    const remember = loginForm.querySelector('input[name="_remember_me"]');
    const signInBtn = loginForm.querySelector('#sign-in-btn');
    const passwordShow = document.getElementById('password-eye-icon');
    const passwordCheckIcon = document.getElementById('password-check-icon');
    const wrongPasswordCrossIcon = document.getElementById('wrong-password-cross-icon');

    /*
     If email entered is valid, then enable:
      1) Password field

     If email entered is invalid, then disable:
       1) Password field
       2) 'remember' checkbox
       3) Sign in button
    */
    function afterValidEmailSignIn()
    {
      if (validateEmail(email.value) == true)
      {
        /* Add success styling to the input border */
        if (!( email.classList.contains('is-valid')))
          email.classList.add('is-valid');

        /* Add success styling to the input icon border */
        if (!( email.previousElementSibling.classList.contains('validated')))
          email.previousElementSibling.classList.add('validated');

        /* Add success styling, in case previous one fail. */
        email.style.setProperty('--validatedInput', '#05A677');

        /* Enable the password field */
        enable(password);

        if (password.value != '')
        {
          if (validatePassword(password.value) == true)
            enable(remember);
            enable(signInBtn);
        }

        // if (validatePassword(password.value) == true)
        //   enable(remember);
        //   enable(signInBtn);
      }
      else if (validateEmail(email.value) == false)
      {
        /*  Remove success styling from the input border */
        if (email.classList.contains('is-valid'))
          email.classList.remove('is-valid');

        /* Remove success styling from the input icon border */
        if (email.previousElementSibling.classList.contains('validated'))
          email.previousElementSibling.classList.remove('validated');

        /* Remove other success styling */
        email.style.setProperty('--validatedInput', '#d1d7e0');

        disable(password);
        disable(remember);
        disable(signInBtn);
      }
    }

    email.addEventListener('input', afterValidEmailSignIn );

    window.addEventListener('load', afterValidEmailSignIn );

    /*
     If password is valid, then enable:
      1) 'remember' checkbox
      2) Sign in button

     If email entered is invalid, then disable:
       1) 'remember' checkbox
       2) Sign in button
    */
    password.addEventListener('input', () =>
    {
      if (validatePassword(password.value) == true)
      {
        /* If password is valid, then add success styling */
        if (!( password.classList.contains('is-valid')))
          password.classList.add('is-valid');

        if (!( password.previousElementSibling.classList.contains('validated')))
          password.previousElementSibling.classList.add('validated');

        if (!( password.nextElementSibling.classList.contains('validated')))
          password.nextElementSibling.classList.add('validated');

        password.style.setProperty('--validatedInput', '#05A677');

        enable(remember);
        enable(signInBtn);
      }
      else if (validatePassword(password.value) == false)
      {
        // passwordCheckIcon.style.display = 'none';
        if (password.classList.contains('is-valid'))
          password.classList.remove('is-valid');

        if (password.previousElementSibling.classList.contains('validated'))
          password.previousElementSibling.classList.remove('validated');

        if (password.nextElementSibling.classList.contains('validated'))
          password.nextElementSibling.classList.remove('validated');

        password.style.setProperty('--validatedInput', '#d1d7e0');

        disable(remember);
        disable(signInBtn);
      }
    });

    /* Show password when eye icon is clicked */
    passwordShow.addEventListener('click', toggleVisibility);

    /* Before submitting the form, reset password input type back
      to 'password' instead of 'text'.*/
    signInBtn.addEventListener('click', (e) =>
    {
      if (password.getAttribute('type') == 'text')
        password.setAttribute('type','password');
    });
}
