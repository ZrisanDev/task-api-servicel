<?php

require_once "database/Database.php";
require_once "helpers/UUID.php";

class Project
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT
                p.*,
                (SELECT COUNT(*) FROM tasks t WHERE t.projectId = p.id) AS total_tasks
            FROM projects p
            ORDER BY p.name
        ");
        return $stmt->fetchAll();
    }

    public function findBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function create($name, $abbreviation)
    {
        $id = UUID::generate();
        $slug = $this->generateSlug($name);

        // Verificar que el slug sea único
        $originalSlug = $slug;
        $counter = 1;
        while ($this->findBySlug($slug)) {
            $slug = $originalSlug . "-" . $counter;
            $counter++;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO projects (id, name, abbreviation, slug) VALUES (?, ?, ?, ?)",
        );
        $success = $stmt->execute([$id, $name, $abbreviation, $slug]);

        if ($success) {
            return [
                "id" => $id,
                "name" => $name,
                "abbreviation" => $abbreviation,
                "slug" => $slug,
            ];
        }
        return false;
    }

    private function generateSlug($name)
    {
        // Convertir a minúsculas
        $slug = strtolower($name);

        // Reemplazar caracteres especiales y espacios con guiones
        $slug = preg_replace("/[^a-z0-9\-]/", "-", $slug);

        // Eliminar guiones múltiples
        $slug = preg_replace("/-+/", "-", $slug);

        // Eliminar guiones al inicio y final
        $slug = trim($slug, "-");

        return $slug;
    }

    public function findById($projectId)
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        return $stmt->fetch();
    }
}
?>
