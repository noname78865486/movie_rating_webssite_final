<form action="add_post_process.php" method="POST">
    <label for="title">Title:</label>
    <input type="text" name="title" required><br>

    <label for="content">Content:</label>
    <textarea name="content" required></textarea><br>

    <label for="visibility">Visibility:</label>
    <select name="visibility">
        <option value="public">Public</option>
        <option value="private">Private</option>
    </select><br>

    <button type="submit">Submit</button>
</form>
