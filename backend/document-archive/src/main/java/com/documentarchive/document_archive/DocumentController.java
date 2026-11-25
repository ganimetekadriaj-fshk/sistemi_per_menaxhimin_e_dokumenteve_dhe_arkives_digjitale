package com.documentarchive.document_archive;

import com.documentarchive.document_archive.audit.AuditLog;
import com.documentarchive.document_archive.audit.AuditRepository;
import org.springframework.core.io.Resource;
import org.springframework.core.io.UrlResource;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
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

    private boolean isAdminRole(String roleHeader) {
        if (roleHeader == null || roleHeader.isBlank()) {
            return true; // Lejon thirrjet direkte/testuese pa header; për UI dërgohet X-User-Role
        }
        return "ADMIN".equalsIgnoreCase(roleHeader.trim());
    }

    // 1. Merr dokumentet (filtron automatikisht të arkivuarat nëse roli është USER)
    @GetMapping
    public List<Document> getAllDocuments(
            @RequestHeader(value = "X-User-Role", required = false) String roleHeader) {
        List<Document> docs = documentRepository.findAll();
        if (roleHeader != null && "USER".equalsIgnoreCase(roleHeader.trim())) {
            return docs.stream()
                    .filter(doc -> !Boolean.TRUE.equals(doc.isArchived()))
                    .toList();
        }
        return docs;
    }

    // 2. Ngarko dokument të ri me skedar fizik dhe Audit Trail
    @PostMapping(consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    public ResponseEntity<?> uploadDocument(
            @RequestHeader(value = "X-User-Role", required = false) String roleHeader,
            @RequestParam("title") String title,
            @RequestParam(value = "description", required = false) String description,
            @RequestParam("categoryId") Long categoryId,
            @RequestParam(value = "uploadedBy", defaultValue = "1") Long uploadedBy,
            @RequestParam("file") MultipartFile file) {
        if (!isAdminRole(roleHeader)) {
            return ResponseEntity.status(HttpStatus.FORBIDDEN)
                    .body("Vetëm administratori mund të ngarkojë dokumente.");
        }
        try {
            String originalFileName = file.getOriginalFilename();
            String uniqueFileName = UUID.randomUUID().toString() + "_" + originalFileName;

            Path filePath = this.fileStorageLocation.resolve(uniqueFileName);
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

    // 4. Fshi dokumentin dhe skedarin fizik (vetëm ADMIN)
    @DeleteMapping("/{id}")
    public ResponseEntity<?> deleteDocument(
            @RequestHeader(value = "X-User-Role", required = false) String roleHeader,
            @PathVariable Long id) {
        if (!isAdminRole(roleHeader)) {
            return ResponseEntity.status(HttpStatus.FORBIDDEN)
                    .body("Vetëm administratori mund të fshijë dokumente.");
        }
        return documentRepository.findById(id).map(doc -> {
            try {
                File file = new File(doc.getStoragePath());
                if (file.exists()) {
                    file.delete();
                }
            } catch (Exception ignored) {}

            documentRepository.delete(doc);

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

    // 6. Arkivo dokumentin (vetëm ADMIN)
    @PutMapping("/archive/{id}")
    public ResponseEntity<?> archiveDocument(
            @RequestHeader(value = "X-User-Role", required = false) String roleHeader,
            @PathVariable Long id) {
        if (!isAdminRole(roleHeader)) {
            return ResponseEntity.status(HttpStatus.FORBIDDEN)
                    .body("Vetëm administratori mund të arkivojë dokumente.");
        }
        return documentRepository.findById(id).map(doc -> {
            doc.setArchived(true);
            documentRepository.save(doc);

            AuditLog log = new AuditLog();
            log.setAction("ARCHIVE_DOCUMENT");
            log.setDetails("U arkivua dokumenti: " + doc.getTitle() + " (ID: " + id + ")");
            auditRepository.save(log);

            return ResponseEntity.ok().body("Dokumenti u arkivua me sukses");
        }).orElse(ResponseEntity.notFound().build());
    }
}