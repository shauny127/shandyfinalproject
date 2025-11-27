--1.) Create a stored procedure that updates a student's age based on their ID.


CREATE PROCEDURE update_student_age(IN student_id INT, IN new_age INT)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Check if the student exists
    IF EXISTS (SELECT 1 FROM students WHERE id = student_id) THEN
        UPDATE students SET age = new_age WHERE id = student_id;
    ELSE
        RAISE EXCEPTION 'Student with ID % does not exist', student_id;
    END IF;
END;
$$;


CALL update_student_age(1, 25);

--2.) Create a stored procedure to delete a student's enrollment.
CREATE PROCEDURE delete_enrollment(IN student_id INT, IN course_id INT)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Check if the enrollment exists
    IF EXISTS (SELECT 1 FROM enrollments WHERE student_id = student_id AND course_id = course_id) THEN
        DELETE FROM enrollments WHERE student_id = student_id AND course_id = course_id;
    ELSE
        RAISE EXCEPTION 'Enrollment for student ID % in course ID % does not exist', student_id, course_id;
    END IF;
END;
$$;

CALL delete_enrollment(1, 1);

--3.) Create a stored procedure that returns the number of students enrolled in a specific course. (using OUT parameter)
CREATE PROCEDURE get_enrollment_count(
    IN course_id INT,
    OUT count INT
)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Check if the course exists
    IF EXISTS (SELECT 1 FROM courses WHERE id = course_id) THEN
        SELECT COUNT(*) INTO count FROM enrollments WHERE course_id = course_id;
    ELSE
        RAISE EXCEPTION 'Course with ID % does not exist', course_id;
    END IF;
END;
$$;
