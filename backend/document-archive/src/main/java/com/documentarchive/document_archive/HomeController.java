package com.documentarchive.document_archive;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
public class HomeController {

    @GetMapping("/")
    public String home() {
        return "Sistemi për menaxhimin e dokumenteve dhe arkivës digjitale është aktiv!";
    }

    @GetMapping("/api/status")
    public String status() {
        return "Backend API funksionon me sukses!";
    }
}
