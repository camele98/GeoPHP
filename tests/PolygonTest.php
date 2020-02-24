<?php

use \JWare\GeoPHP\Polygon;
use \JWare\GeoPHP\Point;
use \JWare\GeoPHP\Line;
use \JWare\GeoPHP\Exceptions\NotEnoughPointsException;
use \JWare\GeoPHP\Exceptions\FirstAndLastPointNotEqualException;
use \JWare\GeoPHP\Exceptions\SettingPointException;

class PolygonTest extends \PHPUnit\Framework\TestCase {
	public function testInstantiationOfPolygon() {
        $somePoint = new Point(0, 0);
        $otherPoint = new Point(1, 0);
        $otherPoint2 = new Point(1, 1);
        
        // Checks correct instance
		$this->assertInstanceOf('\JWare\GeoPHP\Polygon', new Polygon([
            $somePoint,
            $otherPoint,
            $otherPoint2,
            $somePoint
        ]));

        // Checks invalid Polygons
        // Has only 2 different points
        $this->expectException(NotEnoughPointsException::class);
        $invalidPolygon2 = new Polygon([
            new Point(0, 1),
            new Point(3, 1),
            new Point(1, 1) 
        ]);

        $this->expectException(FirstAndLastPointNotEqualException::class);
        $invalidPolygon = new Polygon([
            new Point(0, 1),
            new Point(3, 1),
            new Point(4, 2),
            new Point(1, 1) // Not equals to first point
        ]);
    }

    /**
     * Tests clone method
     */
    public function testClone() {
        $polygon = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0),
        ]);
        $clone = $polygon->clone();
        $this->assertEquals($clone->getPoints(), $polygon->getPoints());
    }

    /**
     * Tests setPoint method
     */
    public function testSetPoint() {
        $polygon = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0),
        ]);
        $newPoint = new Point(4, 1);
        $polygon->setPoint(1, $newPoint);
        $this->assertEquals($polygon->getPoints()[1], $newPoint);

        // Checks invalid Polygon's point setting: It's not allowed to set
        // the first or last Point. You must create a new Polygon
        $this->expectException(SettingPointException::class);
        $polygon->setPoint(0, $newPoint);
        $polygon->setPoint(4, $newPoint);
    }

    /**
     * Tests getCentroid method
     */
    public function testGetCentroid() {
        $polygon = new Polygon([
            new Point(-81, 41),
            new Point(-88, 36),
            new Point(-84, 31),
            new Point(-80, 33),
            new Point(-77, 39),
            new Point(-81, 41)
        ]);
        $centroid = $polygon->getCentroid();
        $this->assertEquals(new Point(-82, 36), $centroid);
    }

    /**
     * Test the area computation
     */
    public function testArea() {
        $polygon1 = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0),
        ]);

        $polygon2 = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(2, 4),
            new Point(0, 0)
        ]);

        $this->assertEquals(16, $polygon1->area());
        $this->assertEquals(8, $polygon2->area());
    }

    /**
     * Test contains method
     */
    public function testContains() {
        $polygon1 = new Polygon([
            new Point(0, 0),
            new Point(4, 0),
            new Point(4, 4),
            new Point(0, 4),
            new Point(0, 0)
        ]);

        $polygon2 = new Polygon([
            new Point(4, 0),
            new Point(8, 0),
            new Point(8, 4),
            new Point(4, 4),
            new Point(4, 0)
        ]);

        $polygon3 = new Polygon([
            new Point(1, 2),
            new Point(2, 2),
            new Point(2, 3),
            new Point(1, 2)
        ]);

        $polygon4 = new Polygon([
            new Point(3, 4),
            new Point(4, 5),
            new Point(3, 5),
            new Point(3, 4)
        ]);

        $polygon5 = new Polygon([
            new Point(1, -1),
            new Point(2, 1),
            new Point(3, -1),
            new Point(2, -2),
            new Point(1, -1)
        ]);

        $line1 = new Line(
            new Point(2, 2),
            new Point(3, 3)
        );

        $line2 = new Line(
            new Point(-1, -1),
            new Point(2, 3)
        );

        $line3 = new Line(
            new Point(1, -1),
            new Point(2, 1)
        );

        // With Point
        $this->assertTrue($polygon1->containsPoint(new Point(2, 2)));
        $this->assertTrue($polygon1->containsPoint(new Point(4, 4)));
        $this->assertTrue($polygon1->containsPoint(new Point(0, 2)));
        $this->assertFalse($polygon1->containsPoint(new Point(10, 12)));
        $this->assertFalse($polygon1->containsPoint(new Point(-1, 2)));
        $this->assertFalse($polygon1->containsPoint(new Point(5, -2)));

        // With Line
        $this->assertTrue($polygon1->containsLine($line1));
        $this->assertFalse($polygon1->containsLine($line2));
        $this->assertFalse($polygon5->containsLine($line3)); // A line as a side of a polygon

        // With Polygon
        $this->assertTrue($polygon1->containsPolygon($polygon3));
        $this->assertFalse($polygon1->containsPolygon($polygon2));
        $this->assertFalse($polygon1->containsPolygon($polygon4));
        $this->assertFalse($polygon1->containsPolygon($polygon5));
    }

    /**
     * Test intersection method
     */
    public function testIntersects() {
        // Polygons
        $polygon1 = new Polygon([
            new Point(2, 4),
            new Point(4, 4),
            new Point(5, 2),
            new Point(3, 1),
            new Point(2.5, 3),
            new Point(2, 4)
        ]);

        $polygon2 = new Polygon([
            new Point(-3, -4),
            new Point(-1, -5),
            new Point(-2, -6),
            new Point(-3, -4)
        ]);

        $polygon3 = new Polygon([
            new Point(4, 3),
            new Point(6, 2.5),
            new Point(6, 1.5),
            new Point(4.5, 1.25),
            new Point(4, 3)
        ]);

        $polygon4 = new Polygon([
            new Point(-1.5, -5.5),
            new Point(-0.5, -6.5),
            new Point(-2, -7),
            new Point(-1.5, -5.5),
        ]);

        $polygon5 = new Polygon([
            new Point(0, 4),
            new Point(0, 0),
            new Point(-1, -2),
            new Point(0, 4),
        ]);

        $polygon6 = new Polygon([
            new Point(0, 4),
            new Point(0, 5),
            new Point(2, 5),
            new Point(0, 4),
        ]);

        $polygon7 = new Polygon([
            new Point(0, 4),
            new Point(0, 5),
            new Point(-1.43, 5),
            new Point(0, 4),
        ]);

        $polygon8 = new Polygon([
            new Point(-0.2248, 0.23657),
            new Point(0, 5),
            new Point(2.0001, 5),
            new Point(-3.2885, 1),
            new Point(-0.2248, 0.23657),
        ]);

        $polygon9 = new Polygon([
            new Point(-8.5678, 6.00001),
            new Point(8, 6),
            new Point(8,0),
            new Point(0, -9.4376535),
            new Point(-13.6999039773659, -8.7824007899917),
            new Point(-8.5678, 6.00001),
        ]); // Polygon with vertices in the 4 quadrants

        $polygon10 = new Polygon([
            new Point(-8.5679, 6.00002),
            new Point(-8.568, 6.00003),
            new Point(-8.569, 6.00004),
            new Point(-8.5691, 6.00004),
            new Point(-8.5679, 6.00002),
        ]); // Polygon very small

        $polygon11 = new Polygon([
            new Point(-10.6904785241294, -10.6080601745131),
            new Point(0,9.5),
            new Point(7.5791315022107, -6.7342746674955),
            new Point(-10.6904785241294, -10.6080601745131),
        ]);

        // Lines
        $line4= new line(
            new point (-4,4),
            new point (5,4),
        );

        // With Point
        $this->assertTrue($polygon1->intersectsPoint(new Point(3, 4)));
        $this->assertFalse($polygon1->intersectsPoint(new Point(5, 4)));

        // With lines
        $this->assertTrue($polygon1->intersectsLine($line4)); // Side of the polygon as part of a line
        $this->assertTrue($polygon11->intersectsLine($line4)); // Multiple intersections
        $this->assertFalse($polygon9->intersectsLine($line4)); // No intersections

        // With Polygon
        $this->assertTrue($polygon1->intersectsPolygon($polygon3)); 
        $this->assertTrue($polygon3->intersectsPolygon($polygon1)); // Multiple intersections
        $this->assertTrue($polygon4->intersectsPolygon($polygon2)); // Intersection of polygons in a point of one side
        $this->assertFalse($polygon1->intersectsPolygon($polygon2));
        $this->assertFalse($polygon3->intersectsPolygon($polygon2));
        $this->assertTrue($polygon5->intersectsPolygon($polygon6)); // Intersection of polygons in vertices
        $this->assertTrue($polygon6->intersectsPolygon($polygon7)); // Two polygons with the same side
        $this->assertTrue($polygon8->intersectsPolygon($polygon8)); // Polygon that intersects itself
        $this->assertTrue($polygon8->intersectsPolygon($polygon5)); 
        $this->assertFalse($polygon9->intersectsPolygon($polygon5)); // Enormous polygon
        $this->assertFalse($polygon9->intersectsPolygon($polygon10)); // Very small polygon; minimum distance between polygons without intersection
        $this->assertTrue($polygon11->intersectsPolygon($polygon9)); // Intersection of polygons in the 4 quadrants
    }
}