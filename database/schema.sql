-- Warranty Activation System Database Schema
-- PostgreSQL 14+

-- Contracts table
CREATE TABLE contracts (
    id VARCHAR(50) PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    customer_name VARCHAR(255),
    has_installation BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_contracts_phone ON contracts(phone);
CREATE INDEX idx_contracts_status ON contracts(status);

-- Warranties table
CREATE TABLE warranties (
    id VARCHAR(50) PRIMARY KEY,
    contract_id VARCHAR(50) NOT NULL REFERENCES contracts(id),
    activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activated_by_ip VARCHAR(45),
    channel VARCHAR(50) DEFAULT 'web',
    discounts_reserved_until TIMESTAMP,
    CONSTRAINT fk_contract FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
);

CREATE INDEX idx_warranties_contract ON warranties(contract_id);
CREATE INDEX idx_warranties_activated ON warranties(activated_at);

-- Warranty feedback table
CREATE TABLE warranty_feedback (
    id SERIAL PRIMARY KEY,
    warranty_id VARCHAR(50) NOT NULL REFERENCES warranties(id),
    sales_rate INTEGER CHECK (sales_rate BETWEEN 1 AND 5),
    delivery_rate INTEGER CHECK (delivery_rate BETWEEN 1 AND 5),
    installation_rate INTEGER CHECK (installation_rate BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_warranty_feedback FOREIGN KEY (warranty_id) REFERENCES warranties(id) ON DELETE CASCADE
);

CREATE INDEX idx_feedback_warranty ON warranty_feedback(warranty_id);
CREATE INDEX idx_feedback_ratings ON warranty_feedback(sales_rate, delivery_rate, installation_rate);

-- Extra work table
CREATE TABLE extra_work (
    id SERIAL PRIMARY KEY,
    warranty_id VARCHAR(50) NOT NULL REFERENCES warranties(id),
    title VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_warranty_extra FOREIGN KEY (warranty_id) REFERENCES warranties(id) ON DELETE CASCADE
);

CREATE INDEX idx_extra_work_warranty ON extra_work(warranty_id);

-- Reserved discounts table
CREATE TABLE reserved_discounts (
    id SERIAL PRIMARY KEY,
    warranty_id VARCHAR(50) NOT NULL REFERENCES warranties(id),
    code VARCHAR(50) NOT NULL,
    discount_percent INTEGER NOT NULL,
    reserved_until TIMESTAMP NOT NULL,
    used_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_warranty_discount FOREIGN KEY (warranty_id) REFERENCES warranties(id) ON DELETE CASCADE
);

CREATE INDEX idx_discounts_warranty ON reserved_discounts(warranty_id);
CREATE INDEX idx_discounts_code ON reserved_discounts(code);
CREATE INDEX idx_discounts_active ON reserved_discounts(is_active, reserved_until);

-- Audit log table
CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    entity VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    performed_by VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    changes JSONB,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_entity ON audit_log(entity, entity_id);
CREATE INDEX idx_audit_action ON audit_log(action);
CREATE INDEX idx_audit_timestamp ON audit_log(performed_at);

-- Rate limiting table (if not using Redis)
CREATE TABLE rate_limits (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    attempt_count INTEGER DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    window_end TIMESTAMP NOT NULL,
    is_blocked BOOLEAN DEFAULT FALSE,
    CONSTRAINT unique_rate_limit UNIQUE(identifier, endpoint, window_start)
);

CREATE INDEX idx_rate_limits_identifier ON rate_limits(identifier, endpoint);
CREATE INDEX idx_rate_limits_window ON rate_limits(window_end);

-- Discount catalog table (for admin management)
CREATE TABLE discount_catalog (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_percent INTEGER NOT NULL,
    category VARCHAR(100),
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    valid_from TIMESTAMP,
    valid_until TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_discount_catalog_code ON discount_catalog(code);
CREATE INDEX idx_discount_catalog_active ON discount_catalog(is_active);

-- Insert default discount catalog
INSERT INTO discount_catalog (code, title, discount_percent, category, is_active) VALUES
    ('glue_10', 'Клей для ламината', 10, 'materials', TRUE),
    ('molding_5', 'Плинтус', 5, 'materials', TRUE),
    ('underlay_5', 'Подложка', 5, 'materials', TRUE),
    ('primer_10', 'Грунтовка', 10, 'materials', TRUE),
    ('installation_30', 'Укладка', 30, 'services', TRUE),
    ('none', 'Ничего не нужно', 0, 'other', TRUE);

-- Insert sample contracts for testing
INSERT INTO contracts (id, phone, customer_name, has_installation, status) VALUES
    ('IL-123456', '+7 (999) 123-45-67', 'Иван Иванов', TRUE, 'active'),
    ('D-789012', '+7 (999) 987-65-43', 'Петр Петров', FALSE, 'active');

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger for contracts table
CREATE TRIGGER update_contracts_updated_at 
    BEFORE UPDATE ON contracts 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger for discount_catalog table
CREATE TRIGGER update_discount_catalog_updated_at 
    BEFORE UPDATE ON discount_catalog 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- Views for reporting

-- View: Warranty activation statistics
CREATE VIEW v_warranty_statistics AS
SELECT 
    DATE(activated_at) as activation_date,
    COUNT(*) as total_activations,
    COUNT(DISTINCT contract_id) as unique_contracts,
    AVG(CASE WHEN wf.sales_rate IS NOT NULL THEN wf.sales_rate END) as avg_sales_rate,
    AVG(CASE WHEN wf.delivery_rate IS NOT NULL THEN wf.delivery_rate END) as avg_delivery_rate,
    AVG(CASE WHEN wf.installation_rate IS NOT NULL THEN wf.installation_rate END) as avg_installation_rate
FROM warranties w
LEFT JOIN warranty_feedback wf ON w.id = wf.warranty_id
GROUP BY DATE(activated_at)
ORDER BY activation_date DESC;

-- View: Popular discounts
CREATE VIEW v_popular_discounts AS
SELECT 
    dc.title,
    dc.code,
    dc.discount_percent,
    COUNT(rd.id) as reservation_count,
    COUNT(CASE WHEN rd.used_at IS NOT NULL THEN 1 END) as used_count
FROM discount_catalog dc
LEFT JOIN reserved_discounts rd ON dc.code = rd.code
WHERE dc.is_active = TRUE
GROUP BY dc.id, dc.title, dc.code, dc.discount_percent
ORDER BY reservation_count DESC;

-- View: Customer feedback summary
CREATE VIEW v_feedback_summary AS
SELECT 
    c.id as contract_id,
    c.customer_name,
    c.phone,
    w.id as warranty_id,
    w.activated_at,
    wf.sales_rate,
    wf.delivery_rate,
    wf.installation_rate,
    wf.comment,
    ROUND((COALESCE(wf.sales_rate, 0) + COALESCE(wf.delivery_rate, 0) + COALESCE(wf.installation_rate, 0))::NUMERIC / 
          NULLIF((CASE WHEN wf.sales_rate IS NOT NULL THEN 1 ELSE 0 END + 
                  CASE WHEN wf.delivery_rate IS NOT NULL THEN 1 ELSE 0 END + 
                  CASE WHEN wf.installation_rate IS NOT NULL THEN 1 ELSE 0 END), 0), 2) as avg_rating
FROM contracts c
INNER JOIN warranties w ON c.id = w.contract_id
LEFT JOIN warranty_feedback wf ON w.id = wf.warranty_id;

-- Comments
COMMENT ON TABLE contracts IS 'Customer contracts and orders';
COMMENT ON TABLE warranties IS 'Activated warranty records';
COMMENT ON TABLE warranty_feedback IS 'Customer feedback ratings';
COMMENT ON TABLE extra_work IS 'Additional work not included in original contract';
COMMENT ON TABLE reserved_discounts IS 'Discounts reserved by customers';
COMMENT ON TABLE audit_log IS 'Audit trail for all system actions';
COMMENT ON TABLE rate_limits IS 'Rate limiting for API endpoints';
COMMENT ON TABLE discount_catalog IS 'Catalog of available discounts';
