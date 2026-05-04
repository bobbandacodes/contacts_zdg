-- ZAMBEZI DIAMOND Group - Standalone Contacts Directory

CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL
);

INSERT IGNORE INTO companies (code, name) VALUES
('ZDG','Zambezi Diamond Group'),
('ZDL','Zambezi Diamond Limited'),
('ZDC','ZDC Limited'),
('IBS','IBS Limited'),
('BR','Blu Reef Limited');

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    position VARCHAR(120) NOT NULL,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    rank_level TINYINT NOT NULL DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
