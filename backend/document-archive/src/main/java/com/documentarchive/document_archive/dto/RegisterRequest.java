
package com.documentarchive.document_archive.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;

public class RegisterRequest {

    @NotBlank(message = "Emri eshte i detyrueshem")
    private String fullName;

    @NotBlank(message = "Email eshte i detyrueshem")
    @Email(message = "Email nuk eshte valid")
    private String email;

    @NotBlank(message = "Fjalekalimi eshte i detyrueshem")
    @Size(min = 8, message = "Fjalekalimi duhet te kete se paku 8 karaktere")
    private String password;

    public RegisterRequest() {
    }

    public String getFullName() {
        return fullName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
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
