;; -*- Scheme -*-
;;
;; $Id$
;;
;; This file contains stylesheet customization common to the HTML
;; and print versions.
;;

(define %use-id-as-filename% #t)
(define %gentext-nav-tblwidth% "100%")
(define %refentry-function% #t)
(define %refentry-generate-name% #f)
(define %funcsynopsis-style% 'ansi)
(define %legalnotice-link-file% (string-append "copyright" %html-ext%))
(define %generate-legalnotice-link% #t)
(define %footnotes-at-end% #t)
(define %force-chapter-toc% #t)
(define newline "\U-000D")

(define (php3-code code)
  (make processing-instruction
    data: (string-append "php " code "?")))
