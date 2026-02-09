

SQL Query Optimization

The provided SQL query can be significantly optimized, primarily by eliminating the inefficient manual timestamp calculation using EXTRACT and replacing it with a built-in function, while also applying other best practices.

1. Replace Manual Timestamp Calculation with GREATEST

The most critical performance issue is the complex greatest(...) expression that manually calculates Unix timestamps from r.created and r.updated using EXTRACT. This forces the database to perform expensive function calls on every row, preventing index usage.

Optimized Approach: Use the GREATEST function directly on the timestamp columns. This is far more efficient and allows potential index usage.

SELECT
    r.rvw_id,
    r.active,
    r.headline,
    r.comment,
    l.vote_like,
    r.rating,
    r.fit,
    r.width,
    r.comfort,
    rc.recommend,
    d.delivery,
    rimg.image,
    rimg.created AS image_created,
    r.created,
    r.updated,
    u.user_id,
    u.title,
    u.email,
    u.first_name,
    u.last_name,
    ua.country,
    pr.pro_id,
    pr.name,
    pr.brand,
    pr.category,
    pr.occasion,
    pr.type,
    -- Efficiently get the later of the two timestamps
    GREATEST(r.created, r.updated) AS latest_timestamp
FROM product_reviews r
LEFT JOIN review_likes l ON r.rvw_id = l.fk_rvw_id
LEFT JOIN review_recommends rc ON r.rvw_id = rc.fk_rvw_id
LEFT JOIN review_delivery d ON r.rvw_id = d.fk_rvw_id
LEFT JOIN review_image rimg ON r.rvw_id = rimg.fk_rvw_id
LEFT JOIN users u ON r.fk_user_id = u.user_id
LEFT JOIN user_address ua ON u.user_id = ua.fk_user_id
LEFT JOIN products pr ON pr.pro_id = r.fk_pro_id
WHERE r.active = :status;


SQL GREATEST function timestamp
optimize EXTRACT function in SQL SELECT site:reddit.com
2. Ensure Proper Indexing

Without indexes, the query will perform full table scans, which is slow for large datasets. Key indexes to create:

-- Primary lookup for reviews by status
CREATE INDEX idx_product_reviews_active ON product_reviews(active);

-- Critical for all JOINs
CREATE INDEX idx_review_likes_fk_rvw_id ON review_likes(fk_rvw_id);
CREATE INDEX idx_review_recommends_fk_rvw_id ON review_recommends(fk_rvw_id);
CREATE INDEX idx_review_delivery_fk_rvw_id ON review_delivery(fk_rvw_id);
CREATE INDEX idx_review_image_fk_rvw_id ON review_image(fk_rvw_id);
CREATE INDEX idx_users_user_id ON users(user_id); -- Usually PK, so exists
CREATE INDEX idx_user_address_fk_user_id ON user_address(fk_user_id);
CREATE INDEX idx_products_pro_id ON products(pro_id); -- Usually PK, so exists

-- For the GREATEST function, consider a computed index if supported
-- (e.g., PostgreSQL: CREATE INDEX ... ON table ((GREATEST(created, updated)));


SQL index best practices for JOINs
3. Select Only Required Columns

The query selects many columns (e.g., u.title, u.email, pr.occasion). Only include columns that are actually needed by the application. Reducing the number of columns decreases I/O, memory usage, and network transfer.

4. Consider Pagination

If the result set is large, add LIMIT and OFFSET (or FETCH FIRST ... ROWS ONLY) to retrieve data in smaller chunks.

-- Add to the end of the query
ORDER BY r.created DESC -- or another logical order
LIMIT 50 OFFSET 0;


SQL pagination best practices
5. Analyze the Execution Plan

Use EXPLAIN (or EXPLAIN ANALYZE in PostgreSQL) to see how the database executes the query. This will show if indexes are being used, where bottlenecks exist, and if full scans are occurring.

EXPLAIN SELECT ... -- your full optimized query


how to read SQL execution plan
1. Replace Manual Timestamp Calculation with GREATEST
2. Ensure Proper Indexing
3. Select Only Required Columns
4. Consider Pagination
5. Analyze the Execution Plan
