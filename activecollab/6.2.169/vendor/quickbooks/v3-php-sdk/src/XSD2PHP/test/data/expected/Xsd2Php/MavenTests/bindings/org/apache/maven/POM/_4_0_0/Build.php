<?php
namespace org\apache\maven\POM\_4_0_0;

/**
 * @xmlNamespace http://maven.apache.org/POM/4.0.0
 * @xmlType
 * @xmlName Build
 * @var org\apache\maven\POM\_4_0_0\Build
 * @xmlDefinition 3.0.0+
 */
class Build
{

    
    /**
     * @Definition
                        This element specifies a directory containing the source
                        of the project. The generated build system will compile
                        the source in this directory when the project is built.
                        The path given is relative to the project descriptor.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName sourceDirectory
     * @var string
     */
    public $sourceDirectory;
    /**
     * @Definition
                        This element specifies a directory containing the script sources
                        of the project. This directory is meant to be different from the
                        sourceDirectory, in that its contents will be copied to the output
                        directory in most cases (since scripts are interpreted rather than
                        compiled).

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName scriptSourceDirectory
     * @var string
     */
    public $scriptSourceDirectory;
    /**
     * @Definition
                        This element specifies a directory containing the unit test
                        source of the project. The generated build system will
                        compile these directories when the project is being tested.
                        The path given is relative to the project descriptor.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName testSourceDirectory
     * @var string
     */
    public $testSourceDirectory;
    /**
     * @Definition
                        The directory where compiled application classes are placed.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName outputDirectory
     * @var string
     */
    public $outputDirectory;
    /**
     * @Definition
                        The directory where compiled test classes are placed.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName testOutputDirectory
     * @var string
     */
    public $testOutputDirectory;
    /**
     * @Definition A set of build extensions to use
                        from this project.
     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName extensions
     */
    public $extensions;
    /**
     * @Definition
                        The default goal (or phase in Maven 2) to execute when none is specified for
                        the project.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName defaultGoal
     * @var string
     */
    public $defaultGoal;
    /**
     * @Definition
                        This element describes all of the classpath resources such as properties files
                        associated with a
                        project. These resources are often included in the final package.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName resources
     */
    public $resources;
    /**
     * @Definition
                        This element describes all of the classpath resources such as properties files
                        associated with a
                        project's unit tests.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName testResources
     */
    public $testResources;
    /**
     * @Definition
                        The directory where all files generated by the build are placed.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName directory
     * @var string
     */
    public $directory;
    /**
     * @Definition
                        The filename (excluding the extension, and with no path information) that the
                        produced artifact
                        will be called. The default value is
                        <code>${artifactId}-${version}</code>.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName finalName
     * @var string
     */
    public $finalName;
    /**
     * @Definition
                        The list of filter properties files that are used when filtering is enabled.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName filters
     */
    public $filters;
    /**
     * @Definition
                        Default plugin information to be made available for reference by
                        projects derived from this one. This plugin configuration will not
                        be resolved or bound to the lifecycle unless referenced. Any local
                        configuration for a given plugin will override the plugin's entire
                        definition here.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName pluginManagement
     * @var org\apache\maven\POM\_4_0_0\PluginManagement
     */
    public $pluginManagement;
    /**
     * @Definition
                        The list of plugins to use.

     * @xmlType element
     * @xmlNamespace http://maven.apache.org/POM/4.0.0
     * @xmlMinOccurs 0
     * @xmlName plugins
     */
    public $plugins;
} // end class Build
