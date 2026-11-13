package com.documentarchive.document_archive;

import com.documentarchive.document_archive.audit.AuditLog;
import com.documentarchive.document_archive.audit.AuditRepository;
import jakarta.validation.Valid;
import org.springframework.core.io.Resource;
import org.springframework.core.io.UrlResource;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.List;
import java.util.UUID;

@RestController
@RequestMapping("/api/documents")
@CrossOrigin(origins = "*")
public class DocumentController {

    private final DocumentRepository documentRepository;
    private final AuditRepository auditRepository;
    private final Path fileStorageLocation;

    public DocumentController(DocumentRepository documentRepository, AuditRepository auditRepository) {
        this.documentRepository = documentRepository;
        this.auditRepository = auditRepository;

        // Rruga direkte dhe e sigurt në C:/document_uploads
        this.fileStorageLocation = Paths.get("C:/document_uploads").toAbsolutePath().normalize();

        try {
            Files.createDirectories(this.fileStorageLocation);
        } catch (Exception ex) {
            throw new RuntimeException("Nuk mund të krijohej folderi ku do të ruhen skedarët.", ex);
        }
    }

    // 1. Merr të gjitha dokumentet
    @GetMapping
    public List<Document> getAllDocuments() {
        return documentRepository.findAll();
    }

    // 2. Ngarko dokument të ri me skedar fizik dhe Audit Trail
    @PostMapping(consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    public ResponseEntity<?> uploadDocument(
            @RequestParam("title") String title,
            @RequestParam(value = "description", required = false) String description,
            @RequestParam("categoryId") Long categoryId,
            @RequestParam(value = "uploadedBy", defaultValue = "1") Long uploadedBy,
            @RequestParam("file") MultipartFile file) {
        try {
            String originalFileName = file.getOriginalFilename();
            String uniqueFileName = UUID.randomUUID().toString() + "_" + originalFileName;
            
            // Përdorim rrugën absolute të sigurt
            Path filePath = this.fileStorageLocation.resolve(uniqueFileName);

            // Ruaj skedarin fizikisht në server
            file.transferTo(filePath.toFile());

            Document doc = new Document();
            doc.setTitle(title);
            doc.setDescription(description);
            doc.setFileName(originalFileName);
            doc.setStoragePath(filePath.toString());
            doc.setContentType(file.getContentType());
            doc.setFileSize(file.getSize());
            doc.setArchived(false);
            doc.setUploadedBy(uploadedBy);
            doc.setCategoryId(categoryId);

            Document savedDoc = documentRepository.save(doc);

            // Regjistro veprimin në Audit Trail
            AuditLog log = new AuditLog();
            log.setAction("CREATE_DOCUMENT");
            log.setDetails("U shtua dokumenti: " + title + " (ID: " + savedDoc.getDocumentId() + ")");
            auditRepository.save(log);

            return ResponseEntity.ok(savedDoc);

        } catch (IOException e) {
            return ResponseEntity.status(500).body("Gabim gjatë ruajtjes së skedarit: " + e.getMessage());
        }
    }

    // 3. Shkarko skedarin fizik nga serveri
    @GetMapping("/download/{id}")
    public ResponseEntity<Resource> downloadFile(@PathVariable Long id) {
        try {
            Document doc = documentRepository.findById(id)
                    .orElseThrow(() -> new RuntimeException("Dokumenti nuk u gjet"));

            Path path = Paths.get(doc.getStoragePath());
            Resource resource = new UrlResource(path.toUri());

            if (resource.exists() || resource.isReadable()) {
                // Regjistro shkarkimin në Audit Trail
                AuditLog log = new AuditLog();
                log.setAction("DOWNLOAD_DOCUMENT");
                log.setDetails("U shkarkua skedari për dokumentin: " + doc.getTitle());
                auditRepository.save(log);

                return ResponseEntity.ok()
                        .contentType(MediaType.APPLICATION_OCTET_STREAM)
                        .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"" + doc.getFileName() + "\"")
                        .body(resource);
            } else {
                return ResponseEntity.notFound().build();
            }
        } catch (Exception e) {
            return ResponseEntity.internalServerError().build();
        }
    }

    // 4. Fshi dokumentin dhe skedarin fizik
    @DeleteMapping("/{id}")
    public ResponseEntity<?> deleteDocument(@PathVariable Long id) {
        return documentRepository.findById(id).map(doc -> {
            // Fshi skedarin fizik nëse ekziston
            try {
                File file = new File(doc.getStoragePath());
                if (file.exists()) {
                    file.delete();
                }
            } catch (Exception ignored) {}

            documentRepository.delete(doc);

            // Regjistro fshirjen në Audit Trail
            AuditLog log = new AuditLog();
            log.setAction("DELETE_DOCUMENT");
            log.setDetails("U fshi dokumenti me ID: " + id);
            auditRepository.save(log);

            return ResponseEntity.ok().body("Dokumenti u fshi me sukses");
        }).orElse(ResponseEntity.notFound().build());
    }

    // 5. Merr të gjitha log-et e auditimit
    @GetMapping("/audit-logs")
    public List<AuditLog> getAllAuditLogs() {
        return auditRepository.findAll();
    }

    // 6. Arkivo dokumentin (Tani ndodhet brenda klasës, siç duhet)
    @PutMapping("/archive/{id}")
    public ResponseEntity<?> archiveDocument(@PathVariable Long id) {
        return documentRepository.findById(id).map(doc -> {
            doc.setArchived(true);
            documentRepository.save(doc);

            // Regjistro veprimin në Audit Trail
            AuditLog log = new AuditLog();
            log.setAction("ARCHIVE_DOCUMENT");
            log.setDetails("U arkivua dokumenti: " + doc.getTitle() + " (ID: " + id + ")");
            auditRepository.save(log);

            return ResponseEntity.ok().body("Dokumenti u arkivua me sukses");
        }).orElse(ResponseEntity.notFound().build());
    }
}