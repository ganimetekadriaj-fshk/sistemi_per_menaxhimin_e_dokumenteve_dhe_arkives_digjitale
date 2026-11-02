
package com.documentarchive.document_archive.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;

public class LoginRequest {

    @NotBlank(message = "Email eshte i detyrueshem")
    @Email(message = "Email nuk eshte valid")
    private String email;

    @NotBlank(message = "Fjalekalimi eshte i detyrueshem")
    private String password;

    public LoginRequest() {
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }
}
