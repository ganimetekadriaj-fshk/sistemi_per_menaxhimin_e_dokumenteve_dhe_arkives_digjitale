package com.documentarchive.document_archive; // Përshtate me paketën tënde

import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.web.bind.annotation.*;

import java.util.Map;

@RestController
@RequestMapping("/api/auth")
@CrossOrigin(origins = "*")
public class AuthController {

    private final UserRepository userRepository;
    private final BCryptPasswordEncoder passwordEncoder = new BCryptPasswordEncoder();

    public AuthController(UserRepository userRepository) {
        this.userRepository = userRepository;
    }

    @PostMapping("/register")
    public ResponseEntity<?> register(@RequestBody RegisterRequest request) {
        if (request.getEmail() == null || request.getEmail().isBlank() ||
            request.getPassword() == null || request.getPassword().length() < 6 ||
            request.getFullName() == null || request.getFullName().isBlank()) {
            return ResponseEntity.badRequest().body(
                Map.of("message", "Plotësoni të gjitha fushat (fjalëkalimi min. 6 karaktere).")
            );
        }

        String normalizedEmail = request.getEmail().trim().toLowerCase();

        // Kontrollo nëse ekziston tashmë përdoruesi me këtë email
        if (userRepository.findByEmail(normalizedEmail).isPresent()) {
            return ResponseEntity.status(HttpStatus.CONFLICT).body(
                Map.of("message", "Ky email është tashmë i regjistruar.")
            );
        }

        User newUser = new User();
        newUser.setFullName(request.getFullName().trim());
        newUser.setEmail(normalizedEmail);
        // Enkriptimi i fjalëkalimit me BCrypt
        newUser.setPassword(passwordEncoder.encode(request.getPassword()));
        // Roli detyrohet gjithmonë USER në regjistrim publik
        newUser.setRole("USER");

        userRepository.save(newUser);

        return ResponseEntity.status(HttpStatus.CREATED).body(
            Map.of(
                "message", "Llogaria u regjistrua me sukses.",
                "email", newUser.getEmail(),
                "role", newUser.getRole()
            )
        );
    }

    public static class RegisterRequest {
        private String fullName;
        private String email;
        private String password;
        private String role; // Injorohet qëllimisht për siguri

        public String getFullName() { return fullName; }
        public void setFullName(String fullName) { this.fullName = fullName; }

        public String getEmail() { return email; }
        public void setEmail(String email) { this.email = email; }

        public String getPassword() { return password; }
        public void setPassword(String password) { this.password = password; }

        public String getRole() { return role; }
        public void setRole(String role) { this.role = role; }
    }
}