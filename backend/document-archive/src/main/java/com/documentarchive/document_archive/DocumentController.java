package com.documentarchive.document_archive;

import java.util.List;

import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/documents")
public class DocumentController {

    private final DocumentRepository documentRepository;

    public DocumentController(DocumentRepository documentRepository) {
        this.documentRepository = documentRepository;
    }

    // Shfaq të gjitha dokumentet
    @GetMapping
    public List<Document> getAllDocuments() {
        return documentRepository.findAll();
    }

    // Gjej një dokument sipas ID-së
    @GetMapping("/{id}")
    public ResponseEntity<Document> getDocumentById(
            @PathVariable Long id) {

        return documentRepository.findById(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    // Shto një dokument
    @PostMapping
    public Document createDocument(@RequestBody Document document) {
        return documentRepository.save(document);
    }

    // Përditëso një dokument
    @PutMapping("/{id}")
    public ResponseEntity<Document> updateDocument(
            @PathVariable Long id,
            @RequestBody Document updatedDocument) {

        return documentRepository.findById(id)
                .map(document -> {
                    document.setTitle(updatedDocument.getTitle());
                    document.setDescription(updatedDocument.getDescription());
                    document.setFileName(updatedDocument.getFileName());
                    document.setStoragePath(updatedDocument.getStoragePath());
                    document.setContentType(updatedDocument.getContentType());
                    document.setFileSize(updatedDocument.getFileSize());
                    document.setArchived(updatedDocument.isArchived());
                    document.setUploadedBy(updatedDocument.getUploadedBy());
                    document.setCategoryId(updatedDocument.getCategoryId());

                    return ResponseEntity.ok(
                            documentRepository.save(document));
                })
                .orElse(ResponseEntity.notFound().build());
    }

    // Fshij një dokument
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> deleteDocument(@PathVariable Long id) {
        if (!documentRepository.existsById(id)) {
            return ResponseEntity.notFound().build();
        }

        documentRepository.deleteById(id);
        return ResponseEntity.noContent().build();
    }
}
