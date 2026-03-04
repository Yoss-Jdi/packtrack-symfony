-- Add Admin Account to PackTrack
-- This script creates an admin user with the following credentials:
-- Email: admin@packtrack.com
-- Password: Admin123!
-- 
-- IMPORTANT: Change the password after first login!

-- Insert admin user
-- Password hash for "Admin123!" using bcrypt
INSERT INTO utilisateurs (
    Email, 
    MotDePasse, 
    Nom, 
    Prenom, 
    telephone, 
    role, 
    photo, 
    createdAt
) VALUES (
    'admin@packtrack.com',
    '$2y$13$8K1p/a0dL9meIKgwhQhUjetIxodY0EkV7QdkLEsYgqK7r/A7rne1.',
    'Admin',
    'System',
    '+216 12345678',
    'ADMIN',
    NULL,
    NOW()
);

-- Verify the admin was created
SELECT id_utilisateur, Email, Nom, Prenom, role, createdAt 
FROM utilisateurs 
WHERE Email = 'admin@packtrack.com';
