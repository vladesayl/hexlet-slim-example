<?php
    
     function validate(array $user)
    {
        $errors = [];
        if ($user['nickname'] === '' || $user['email'] === '') {
            $errors['name'] = "Can't be blank";
        }
        return $errors;
        // END
    }
