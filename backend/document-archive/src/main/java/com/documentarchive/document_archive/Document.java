
package com.documentarchive.document_archive;

import java.time.LocalDateTime;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.PositiveOrZero;
import jakarta.validation.constraints.Size;

@Entity
@Table(name = "Documents")
public class Document {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "document_id")
    private Long documentId;

    @NotBlank(message = "Titulli eshte i detyrueshem")
    @Size(max = 255, message = "Titulli nuk duhet te kaloje 255 karaktere")
    @Column(name = "title", nullable = false)
    private String title;

    @Column(name = "description")
    private String description;

    @NotBlank(message = "Emri i skedarit eshte i detyrueshem")
    @Size(max = 255)
    @Column(name = "file_name", nullable = false)
    private String fileName;

    @NotBlank(message = "Rruga e ruajtjes eshte e detyrueshme")
    @Size(max = 500)
    @Column(name = "storage_path", nullable = false, length = 500)
    private String storagePath;

    @Size(max = 100)
    @Column(name = "content_type", length = 100)
    private String contentType;

    @NotNull(message = "Madhesia e skedarit eshte e detyrueshme")
    @PositiveOrZero(message = "Madhesia nuk mund te jete negative")
    @Column(name = "file_size", nullable = false)
    private Long fileSize;

    @Column(name = "uploaded_at", insertable = false, updatable = false)
    private LocalDateTime uploadedAt;

    @Column(name = "is_archived", nullable = false)
    private boolean archived;

    @NotNull(message = "ID-ja e perdoruesit eshte e detyrueshme")
    @Column(name = "uploaded_by", nullable = false)
    private Long uploadedBy;

    @NotNull(message = "ID-ja e kategorise eshte e detyrueshme")
    @Column(name = "category_id", nullable = false)
    private Long categoryId;

    public Document() {
    }

    public Long getDocumentId() { return documentId; }
    public void setDocumentId(Long documentId) { this.documentId = documentId; }

    public String getTitle() { return title; }
    public void setTitle(String title) { this.title = title; }

    public String getDescription() { return description; }
    public void setDescription(String description) { this.description = description; }

    public String getFileName() { return fileName; }
    public void setFileName(String fileName) { this.fileName = fileName; }

    public String getStoragePath() { return storagePath; }
    public void setStoragePath(String storagePath) { this.storagePath = storagePath; }

    public String getContentType() { return contentType; }
    public void setContentType(String contentType) { this.contentType = contentType; }

    public Long getFileSize() { return fileSize; }
    public void setFileSize(Long fileSize) { this.fileSize = fileSize; }

    public LocalDateTime getUploadedAt() { return uploadedAt; }
    public void setUploadedAt(LocalDateTime uploadedAt) { this.uploadedAt = uploadedAt; }

    public boolean isArchived() { return archived; }
    public void setArchived(boolean archived) { this.archived = archived; }

    public Long getUploadedBy() { return uploadedBy; }
    public void setUploadedBy(Long uploadedBy) { this.uploadedBy = uploadedBy; }

    public Long getCategoryId() { return categoryId; }
    public void setCategoryId(Long categoryId) { this.categoryId = categoryId; }
}
