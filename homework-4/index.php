<?php
session_start();

$pageTitle = 'Books';

require 'includes/header.php';

if (isset($_GET['author']) && $_GET['author'] != 'all-authors') {
    $authorId = (int) $_GET['author'];
    $authorId = mysqli_real_escape_string($connection, $authorId);
    
    if (!authorExistById($connection, $authorId, $messages)) {
        $_SESSION['messages'] = $messages['authorNotExist'];
        header('Location: index.php');
        exit;
    }
    
    if (!authorHasBooks($connection, $authorId, $messages)) {
        $_SESSION['messages'] = $messages['authorHasNotBooks'];
        header('Location: index.php');
        exit;
    }
    
    $filterByAuthorId = "WHERE bks_aut.author_id = $authorId";
} else {
    $filterByAuthorId = '';
}

if (isset($_GET['sort']) && $_GET['sort'] == 'DESC') {
    $currentSort = 'DESC';
    $nextSort = 'ASC';
} else {
    $currentSort = 'ASC';
    $nextSort = 'DESC';
}

$sql = "
    SELECT *
    FROM `books` AS bks
    
    LEFT JOIN `books_authors` AS bks_aut
    ON bks.book_id = bks_aut.book_id
    
    LEFT JOIN `authors` AS aut
    ON bks_aut.author_id = aut.author_id
    
    $filterByAuthorId
        
    ORDER BY bks.book_title $currentSort
";

$query = mysqli_query($connection, $sql);

if (!$query) {
    $_SESSION['messages'] = $messages['wrongQueryExecution'];
    header('Location: ../index.php');
    exit;
}

$allInfo = array();

while ($row = $query->fetch_assoc()) {
    $allInfo[$row['book_id']]['bookTitle'] = $row['book_title'];    
    $allInfo[$row['book_id']]['authorInfo'][$row['author_id']] = $row['author_name'];
}
?>

<h2>All Books</h2>

<?php if (!empty($allInfo)) { ?>
    <div id="filter">
        <form method="GET" action="">
            <select name="author">
                <option value="all-authors">All Authors</option>
                
                <?php $allAuthorsData = getAllAuthors($connection, $messages); ?>
                
                <?php for ($i = 0; $i < count($allAuthorsData['authorId']); $i++) { ?>
                <?php
                    if (isset($authorId)) {                        
                        if ($allAuthorsData['authorId'][$i] == $authorId) {                            
                            $selected = 'selected';
                        } else {
                            $selected = '';
                        }
                    } else {
                        $selected = '';
                    }
                ?>                
                    <option value="<?php echo $allAuthorsData['authorId'][$i]; ?>" <?php echo $selected; ?>>
                        <?php echo $allAuthorsData['authorName'][$i]; ?>
                    </option>
                <?php } ?>
            </select>
            
            <input type="submit" value="Filter" />
        </form>
    </div><!-- #filter -->

    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>
                    <?php
                    if (isset($authorId)) {
                        $authorIdUrl = '&author=' . $authorId;
                    } else {
                        $authorIdUrl = '';
                    }
                    ?>
                    <a href="index.php?sort=<?php echo $nextSort, $authorIdUrl; ?>"
                       title="Sort books in <?php echo $nextSort; ?> order">
                        Book
                    </a>
                </th>
                <th>Authors</th>
            </tr>
        </thead>

        <tbody>
            <?php $booksCounter = 0; ?>
            <?php foreach ($allInfo as $key => $value) { // $key is not used yet ?>
                <?php $booksCounter++; ?>
                <tr>
                    <td><?php echo $booksCounter; ?>.</td>
                    <td><?php echo $value['bookTitle'] ?></td>
                    <td>
                        <?php
                        foreach ($value['authorInfo'] as $kk => $vv) {
                            // Store links in array so that afterwords
                            // to avoid the last comma
                            $authorsLinks[] = '<a href=index.php?author=' . $kk . ' title="Books from ' . $vv . '">' . $vv . '</a>';
                        }

                        $authorsLinksResult = implode(',&nbsp;&nbsp;', $authorsLinks);                    
                        echo $authorsLinksResult;

                        unset($authorsLinks);
                        unset($authorsLinksResult);                    
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <div id="no-books">
        <p>Currently there are no books in the catalog.</p>
        <p>Be the one to add first book <a title="Add Book" href="add-book.php">here</a>.</p>
    </div>
<?php } ?>

<?php
require 'includes/footer.php';